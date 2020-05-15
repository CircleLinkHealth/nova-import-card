<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use App\Events\PatientUserCreated;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Exceptions\PatientAlreadyExistsException;
use CircleLinkHealth\Customer\Repositories\UserRepository;
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
     * How many times to try the importing process.
     */
    private const ATTEMPTS = 3;
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

    /**
     * Attempt to Import a CCDA.
     *
     * This calls the importing process wrapped in a DB transaction.
     *
     * @throws \Throwable
     */
    public function attemptImport(): Ccda
    {
        \DB::transaction(\Closure::fromCallable([$this, 'importCcda']), self::ATTEMPTS);

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
        $newUserId = (string) Str::uuid();

        $email = $this->patientEmail();

        if (optional($this->enrollee)->email) {
            $email = $this->enrollee->email;
        }

        if (empty($email)) {
            $email = $newUserId.'@careplanmanager.com';
        }

        $demographics = $this->ccda->bluebuttonJson()->demographics;

        $this->patient = (new UserRepository())->createNewUser(
            new ParameterBag(
                [
                    'email'        => $email,
                    'password'     => Str::random(30),
                    'display_name' => ucwords(
                        strtolower($this->ccda->patient_first_name.' '.$this->ccda->patient_last_name)
                    ),
                    'first_name' => $this->ccda->patient_first_name,
                    'last_name'  => $this->ccda->patient_last_name,
                    'mrn_number' => $this->ccda->patient_mrn,
                    'birth_date' => $this->ccda->patientDob(),
                    'username'   => empty($email)
                        ? $newUserId
                        : $email,
                    'program_id'        => $this->ccda->practice_id,
                    'is_auto_generated' => true,
                    'roles'             => [Role::whereName('participant')->firstOrFail()->id],
                    'is_awv'            => Ccda::IMPORTER_AWV === $this->ccda->source,
                    'address'           => array_key_exists(0, $demographics->address->street)
                        ? $demographics->address->street[0]
                        : null,
                    'address2' => array_key_exists(1, $demographics->address->street)
                        ? $demographics->address->street[1]
                        : null,
                    'city'  => $demographics->address->city,
                    'state' => $demographics->address->state,
                    'zip'   => $demographics->address->zip,
                ]
            )
        );

        $this->ccda->patient_id = $this->patient->id;
    }

    private function handleDuplicateEnrollees()
    {
        $enrollee = Enrollee::duplicates($this->patient, $this->ccda)->when(
            $this->enrollee instanceof Enrollee,
            function ($q) {
                $q->where('id', '!=', $this->enrollee->id);
            }
        )->first();

        if ($enrollee) {
            $this->throwExceptionIfSuspicious($enrollee);
            $this->enrollee              = $enrollee;
            $enrollee->user_id           = $this->patient->id;
            $enrollee->medical_record_id = $this->ccda->id;
            $enrollee->save();
        }

        if ($this->enrollee) {
            $this->ccda = ImportService::replaceCpmValues($this->ccda, $this->enrollee);

            if ($this->enrollee->medical_record_id != $this->ccda->id) {
                $this->enrollee->medical_record_id = $this->ccda->id;
                $this->enrollee->save();
            }
        }

        return $this;
    }

    /**
     * Store AllergyImports as Allergy Models.
     *
     * @return $this
     */
    private function importAllergies()
    {
        ImportAllergies::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Store Billing Provider.
     *
     * @return $this
     */
    private function importBillingProvider()
    {
        AttachBillingProvider::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Outlines the procedure of creating a Care Plan from a CCDA.
     *
     * @throws \Exception
     * @return Ccda
     */
    private function importCcda()
    {
        if (is_null($this->patient)) {
            try {
                $this->createNewPatient();
            } catch (ValidationException $e) {
                $this->ccda->validation_checks = $e->errors();
                $this->ccda->save();

                return $this->ccda;
            } catch (PatientAlreadyExistsException $e) {
                $this->ccda->patient_id = $e->getPatientUserId();
                $this->ccda->save();
                $this->ccda->load('patient');
                $this->patient = $this->ccda->patient;
            }
        }

        $this->patient->loadMissing(['primaryPractice', 'patientInfo']);
        $this->ccda->loadMissing(['location', 'patient']);

        $this->handleDuplicateEnrollees()
            ->createNewCarePlan()
            ->importAllergies()
            ->importProblems()
            ->importMedications()
            ->importBillingProvider()
            ->importLocation()
            ->importPractice()
            ->importPatientInfo()
            ->importPatientContactWindows()
            ->importPhones()
            ->importInsurance()
//            Commented out because the Vitals we were importing were either
//                 - Not relevant to us (eg. height)
//                 - Outdated and removed by nurses post import
//                 - Not useful because in the case of BP we don't have a starting value.
//            Uncomment after we have refactored vitals/observations.
//            ->importVitals()
            ->updateCcdaPostImport()
            ->updateEnrolleePostImport()
            ->updatePatientUserPostImport();

        event(new PatientUserCreated($this->patient));
    }

    /**
     * Stores Insurance.
     *
     * @return $this
     */
    private function importInsurance()
    {
        ImportInsurances::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Store Location.
     *
     * @return $this
     */
    private function importLocation()
    {
        AttachLocation::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Stores MedicationImports as Medication Models.
     *
     * @return $this
     */
    private function importMedications()
    {
        ImportMedications::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Store Contact Windows.
     *
     * @return $this
     */
    private function importPatientContactWindows()
    {
        AttachDefaultPatientContactWindows::for($this->patient, $this->ccda, $this->enrollee);

        return $this;
    }

    /**
     * Store Patient Info.
     *
     * @return $this
     */
    private function importPatientInfo()
    {
        ImportPatientInfo::for($this->patient, $this->ccda, $this->enrollee);

        return $this;
    }

    /**
     * Store Phone Numbers.
     *
     * @return $this
     */
    private function importPhones()
    {
        ImportPhones::for($this->patient, $this->ccda);

        return $this;
    }

    private function importPractice()
    {
        AttachPractice::for($this->patient, $this->ccda);

        return $this;
    }

    /**
     * Store ProblemImports as Problem Models.
     *
     * @return $this
     */
    private function importProblems()
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
    private function importVitals()
    {
        ImportVitals::for($this->patient, $this->ccda);

        return $this;
    }

    private function patientEmail()
    {
        $email = $this->ccda->patient_email;

        if (in_array(strtolower($email), ['noemail@noemail.com', 'null'])) {
            return null;
        }

        return $email;
    }

    private function throwExceptionIfSuspicious(Enrollee $enrollee)
    {
        if (strtolower($this->patient->last_name) != strtolower($enrollee->last_name)) {
            throw new \Exception("Something fishy is going on. enrollee:{$enrollee->id} has user:{$enrollee->user_id}, which does not matched with user:{$this->patient->id}");
        }

        if (strtolower($this->patient->first_name) == strtolower($enrollee->first_name)) {
            return;
        }

        //middle name
        if (3 === levenshtein(strtolower($this->patient->first_name), strtolower($enrollee->first_name))) {
            return;
        }

        throw new \Exception("Something fishy is going on. enrollee:{$enrollee->id} has user:{$enrollee->user_id}, which does not matched with user:{$this->patient->id}");
    }

    private function updateCcdaPostImport()
    {
        //This CarePlan is now ready to be QA'ed by a CLH Admin
        $this->ccda->imported = true;
        if (in_array($this->patient->carePlan->status, [CarePlan::QA_APPROVED, CarePlan::PROVIDER_APPROVED])) {
            $this->ccda->status = Ccda::CAREPLAN_CREATED;
        } else {
            $this->ccda->status = Ccda::QA;
        }
        if ( ! $this->ccda->mrn) {
            $this->ccda->mrn = $this->patient->patientInfo->mrn_number;
        }
        if ($this->ccda->isDirty()) {
            $this->ccda->save();
        }

        return $this;
    }

    private function updateEnrolleePostImport()
    {
        if ( ! $this->enrollee) {
            return $this;
        }
        $this->enrollee->medical_record_type = get_class($this->ccda);
        $this->enrollee->medical_record_id   = $this->ccda->id;
        $this->enrollee->user_id             = $this->ccda->patient_id;
        $this->enrollee->provider_id         = $this->ccda->billing_provider_id;
        $this->enrollee->location_id         = $this->ccda->location_id;
        if ($this->enrollee->isDirty()) {
            $this->enrollee->save();
        }

        return $this;
    }

    private function updatePatientUserPostImport()
    {
        //Make sure Patient does not have survey-only role moving forward
        $participantRoleId = Role::whereName('participant')->firstOrFail()->id;

        $this->patient->roles()->sync([
            $participantRoleId => ['program_id' => $this->patient->program_id,
            ],
        ]);

        if ($this->patient->isDirty()) {
            $this->patient->save();
        }

        return $this;
    }
}
