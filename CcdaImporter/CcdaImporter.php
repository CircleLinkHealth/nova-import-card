<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use App\CLH\Repositories\UserRepository;
use App\Events\PatientUserCreated;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachBillingProvider;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachDefaultPatientContactWindows;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachLocation;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachPractice;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\FirstOrCreateCarePlan;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportAllergies;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportInsurances;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportMedications;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPhones;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportProblems;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportVitals;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\ImportService;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\ParameterBag;

class CcdaImporter
{
    /**
     * @var CarePlan|\Illuminate\Database\Eloquent\Model
     */
    protected $carePlan;
    /**
     * @var Ccda
     */
    protected $ccda;
    /**
     * @var \CircleLinkHealth\Eligibility\Entities\Enrollee
     */
    protected $enrollee;
    /**
     * @var User
     */
    protected $patient;
    /**
     * @var StringManipulation
     */
    protected $str;

    public function __construct(
        Ccda $ccda,
        User $patient = null,
        Enrollee $enrollee = null
    ) {
        $this->str      = new StringManipulation();
        $this->ccda     = $ccda;
        $this->patient  = $patient;
        $this->enrollee = $enrollee;
    }

    public function attemptCreateCarePlan(): Ccda
    {
        \DB::transaction(
            function () {
                if (is_null($this->patient)) {
                    try {
                        $this->createNewPatient();
                    } catch (ValidationException $e) {
                        $this->ccda->validation_checks = $e->errors();
                        $this->ccda->save();

                        return $this->ccda;
                    }
                }

                $this->patient->loadMissing(['primaryPractice', 'patientInfo']);
                $this->ccda->loadMissing(['location', 'patient']);

                $this->handleEnrollees()
                    ->createNewCarePlan()
                    ->storeAllergies()
                    ->storeProblemsList()
                    ->storeMedications()
                    ->storeBillingProvider()
                    ->storeLocation()
                    ->storePractice()
                    ->storePatientInfo()
                    ->storeContactWindows()
                    ->storePhones()
                    ->storeInsurance()
//                     ->storeVitals()
                ;

                //This CarePlan is now ready to be QA'ed by a CLH Admin
                $this->ccda->imported = true;
                if (in_array($this->patient->carePlan->status, [CarePlan::QA_APPROVED, CarePlan::PROVIDER_APPROVED])) {
                    $this->ccda->status = Ccda::CAREPLAN_CREATED;
                } else {
                    $this->ccda->status = Ccda::QA;
                }
                $this->ccda->save();

                if ($this->enrollee) {
                    $this->enrollee->medical_record_type = get_class($this->ccda);
                    $this->enrollee->medical_record_id = $this->ccda->id;
                    $this->enrollee->user_id = $this->ccda->patient_id;
                    $this->enrollee->provider_id = $this->ccda->billing_provider_id;
                    $this->enrollee->location_id = $this->ccda->location_id;
                    $this->enrollee->save();
                }

                if ($this->patient->isDirty()) {
                    $this->patient->save();
                }

                event(new PatientUserCreated($this->patient));
            }
        );

        return $this->ccda;
    }

    /**
     * Create a new CarePlan.
     *
     * @return $this
     */
    private function createNewCarePlan()
    {
        $this->carePlan = FirstOrCreateCarePlan::for($this->patient, $this->ccda);

        return $this;
    }

    private function createNewPatient()
    {
        $newUserId = Str::random(25);

        $email = empty($email = $this->ccda->patientEmail())
            ? $newUserId.'@careplanmanager.com'
            : $email;

        $this->patient = (new UserRepository())->createNewUser(
            new ParameterBag(
                [
                    'email'        => $email,
                    'password'     => Str::random(30),
                    'display_name' => ucwords(
                        strtolower($this->ccda->patientFirstName().' '.$this->ccda->patientLastName())
                    ),
                    'first_name' => $this->ccda->patientFirstName(),
                    'last_name'  => $this->ccda->patientLastName(),
                    'mrn_number' => $this->ccda->patientMrn(),
                    'birth_date' => $this->ccda->patientDob(),
                    'username'   => empty($email)
                        ? $newUserId
                        : $email,
                    'program_id'        => $this->ccda->practice_id,
                    'is_auto_generated' => true,
                    'roles'             => [Role::whereName('participant')->firstOrFail()->id],
                    'is_awv'            => Ccda::IMPORTER_AWV === $this->ccda->source,
                ]
            )
        );

        Ccda::where('id', $this->ccda->id)->update(
            [
                'patient_id' => $this->patient->id,
            ]
        );
    }

    private function handleEnrollees()
    {
        $enrollee = Enrollee::duplicates($this->patient, $this->ccda)->when(
            $this->enrollee instanceof Enrollee,
            function ($q) {
                $q->where('id', '!=', $this->enrollee->id);
            }
        )->first();

        if ($enrollee) {
            if (strtolower($this->patient->first_name) != strtolower($enrollee->first_name) || strtolower(
                $this->patient->last_name
            ) != strtolower(
                $enrollee->last_name
            )) {
                throw new \Exception("Something fishy is going on. enrollee:{$enrollee->id} has user:{$enrollee->user_id}, which does not matched with user:{$this->patient->id}");
            }
            $this->enrollee    = $enrollee;
            $enrollee->user_id = $this->patient->id;
            $enrollee->save();
        }

        if ($this->enrollee) {
            $this->ccda = ImportService::replaceCpmValues($this->ccda, $this->enrollee);
        }

        return $this;
    }

    /**
     * Store AllergyImports as Allergy Models.
     *
     * @return $this
     */
    private function storeAllergies()
    {
        ImportAllergies::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Store Billing Provider.
     *
     * @return $this
     */
    private function storeBillingProvider()
    {
        AttachBillingProvider::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Store Contact Windows.
     *
     * @return $this
     */
    private function storeContactWindows()
    {
        AttachDefaultPatientContactWindows::for($this->patient, $this->ccda, $this->enrollee);

        return $this;
    }

    /**
     * Stores Insurance.
     *
     * @return $this
     */
    private function storeInsurance()
    {
        ImportInsurances::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Store Location.
     *
     * @return $this
     */
    private function storeLocation()
    {
        AttachLocation::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Stores MedicationImports as Medication Models.
     *
     * @return $this
     */
    private function storeMedications()
    {
        ImportMedications::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Store Patient Info.
     *
     * @return $this
     */
    private function storePatientInfo()
    {
        ImportPatientInfo::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Store Phone Numbers.
     *
     * @return $this
     */
    private function storePhones()
    {
        ImportPhones::for($this->patient, $this->ccda);

        return $this;
    }

    private function storePractice()
    {
        AttachPractice::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Store ProblemImports as Problem Models.
     *
     * @return $this
     */
    private function storeProblemsList()
    {
        ImportProblems::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Store Vitals.
     *
     * @todo: This only applies to CCDAs. Find a cleaner solution. This doesn't fit here.
     *
     * @return $this
     */
    private function storeVitals()
    {
        ImportVitals::for($this->patient, $this->ccda);

        return $this;
    }
}
