<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use App\Events\PatientUserCreated;
use App\SelfEnrollment\Domain\CreateSurveyOnlyUserFromEnrollee;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\AppConfig\CarePlanAutoApprover;
use CircleLinkHealth\Customer\Entities\Patient;
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
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\CcdToLogTranformer;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\ParameterBag;

class CcdaImporter
{
    const EMAIL_EXISTS_BUT_NOT_FAMILY_PREFIX = 'email_taken_';
    const FAMILY_EMAIL_SUFFIX                = '+family';
    /**
     * How many times to try the importing process.
     */
    private const ATTEMPTS = 2;
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
     * @var StringManipulation
     */
    protected $str;

    public function __construct(
        Ccda $ccda,
        Enrollee &$enrollee = null
    ) {
        $this->str      = new StringManipulation();
        $this->ccda     = $ccda;
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

    public static function convertFamilyEmailToValidEmail(string $emailAddress)
    {
        return preg_replace('/\+family\d*/', '', $emailAddress);
    }

    public static function convertToFamilyEmail(string $email)
    {
        return substr_replace(
            $email,
            self::FAMILY_EMAIL_SUFFIX.random_int(1000, 999999),
            strpos($email, '@'),
            0
        );
    }

    /**
     * Returns true if both names are the same, but one includes a middle initial.
     *
     * Example 1: "Foo", "Foo J"
     * Example 2: "Jane", "Jane, J."
     */
    public static function isSameNameButOneHasMiddleInitial(?string $name1, ?string $name2): bool
    {
        if (empty($name1) || empty($name2)) {
            return false;
        }

        //If none of the strings contain spaces, we assume none contain a middle initial
        if ( ! Str::contains($name1, ' ') && ! Str::contains($name2, ' ')) {
            return false;
        }

        return 1 === levenshtein(extractLetters(strtolower($name1)), extractLetters(strtolower($name2)));
    }

    /**
     * Create a new CarePlan.
     *
     * @return $this
     */
    private function createNewCarePlan()
    {
        $this->carePlan = FirstOrCreateCarePlan::for($this->ccda->patient, $this->ccda);

        return $this;
    }

    private function createNewPatient()
    {
        $newUserId = (string) Str::uuid();

        $email = null;

        if (optional($this->enrollee)->email) {
            $email = CreateSurveyOnlyUserFromEnrollee::sanitizeEmail($this->enrollee->id, $this->enrollee->email);
        }

        if (empty($email)) {
            $email = $this->patientEmail();
        }

        if (empty($email)) {
            $email = $newUserId.'@careplanmanager.com';
        }

        $demographics = $this->ccda->bluebuttonJson()->demographics;

        if ($this->isFamily($email, $demographics, $this->enrollee)) {
            $email = self::convertToFamilyEmail($email);
        } elseif ($this->emailIsTaken($email)) {
            $email = self::EMAIL_EXISTS_BUT_NOT_FAMILY_PREFIX.$email;
        }

        $newPatientUser = (new UserRepository())->createNewUser(
            new ParameterBag(
                [
                    'email'        => $email,
                    'password'     => Str::random(30),
                    'display_name' => ucwords(
                        strtolower($this->ccda->patient_first_name.' '.$this->ccda->patient_last_name)
                    ),
                    'first_name'        => $this->ccda->patient_first_name,
                    'last_name'         => $this->ccda->patient_last_name,
                    'mrn_number'        => $this->ccda->patient_mrn,
                    'birth_date'        => $this->ccda->patientDob(),
                    'username'          => $newUserId,
                    'program_id'        => $this->ccda->practice_id,
                    'is_auto_generated' => true,
                    'roles'             => [Role::byName('participant')->id],
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

        $this->ccda->patient_id = $newPatientUser->id;
        $this->ccda->setRelation('patient', $newPatientUser);
    }

    private function emailIsTaken(string $email)
    {
        return User::ofType(['participant', 'survey-only'])
            ->where('email', $email)
            ->where('first_name', '!=', $this->ccda->patient_first_name)
            ->where('last_name', '!=', $this->ccda->patient_last_name)
            ->exists();
    }

    private function ensurePatientHasParticipantRole()
    {
        if ($this->ccda->patient->isParticipant()) {
            return;
        }

        $this->ccda->patient->roles()->sync([
            Role::byName('participant')->id => [
                'program_id' => $this->ccda->patient->program_id,
            ],
        ]);

        \DB::commit();
        $this->ccda->patient->clearRolesCache();
    }

    private function handleDuplicateEnrollees()
    {
        $enrollee = Enrollee::duplicates($this->ccda->patient, $this->ccda)->when(
            $this->enrollee instanceof Enrollee,
            function ($q) {
                $q->where('id', '!=', $this->enrollee->id);
            }
        )->first();

        if ($enrollee) {
            $this->throwExceptionIfSuspicious($enrollee);
            $this->enrollee                    = $enrollee;
            $this->enrollee->user_id           = $this->ccda->patient->id;
            $this->enrollee->medical_record_id = $this->ccda->id;
        }

        if ($this->enrollee) {
            $this->ccda = ImportService::replaceCpmValues($this->ccda, $this->enrollee);

            if ($this->enrollee->medical_record_id != $this->ccda->id) {
                $this->enrollee->medical_record_id = $this->ccda->id;
            }

            if ( ! $this->enrollee->user_id) {
                $this->enrollee->user_id = $this->ccda->patient->id;
                $this->enrollee->setRelation('user', $this->ccda->patient);
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
        ImportAllergies::for($this->ccda->patient, $this->ccda);

        return $this;
    }

    /**
     * Store Billing Provider.
     *
     * @return $this
     */
    private function importBillingProvider()
    {
        AttachBillingProvider::for($this->ccda->patient, $this->ccda);

        return $this;
    }

    /**
     * Outlines the procedure of creating a Care Plan from a CCDA.
     *
     * @throws \Exception
     *
     * @return Ccda
     */
    private function importCcda()
    {
        if (is_null($this->ccda->patient)) {
            try {
                $this->createNewPatient();
            } catch (ValidationException $e) {
                $this->ccda->validation_checks = $e->errors();
                $this->ccda->save();

                return $this->ccda;
            } catch (PatientAlreadyExistsException $e) {
                $this->ccda->patient_id = $e->getPatientUserId();
                $this->ccda->load(['patient.primaryPractice', 'patient.patientInfo']);
            }
        }

        $this->ensurePatientHasParticipantRole();

        $this->ccda->loadMissing(['patient.primaryPractice', 'patient.patientInfo']);

        if (is_null($this->ccda->patient)) {
            throw new \Exception("Could not create patient for CCDA[{$this->ccda->id}]");
        }

        $this
            ->handleDuplicateEnrollees()
            ->updateAddressesFromCareAmbassadorInput()
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
            ->updatePatientUserPostImport()
            ->raiseConcernsOrAutoQAApprove();

        event(new PatientUserCreated($this->ccda->patient));
    }

    /**
     * Stores Insurance.
     *
     * @return $this
     */
    private function importInsurance()
    {
        ImportInsurances::for($this->ccda->patient, $this->ccda);

        return $this;
    }

    /**
     * Store Location.
     *
     * @return $this
     */
    private function importLocation()
    {
        AttachLocation::for($this->ccda->patient, $this->ccda);

        return $this;
    }

    /**
     * Stores MedicationImports as Medication Models.
     *
     * @return $this
     */
    private function importMedications()
    {
        ImportMedications::for($this->ccda->patient, $this->ccda);

        return $this;
    }

    /**
     * Store Contact Windows.
     *
     * @return $this
     */
    private function importPatientContactWindows()
    {
        AttachDefaultPatientContactWindows::for($this->ccda->patient, $this->ccda, $this->enrollee);

        return $this;
    }

    /**
     * Store Patient Info.
     *
     * @return $this
     */
    private function importPatientInfo()
    {
        ImportPatientInfo::for($this->ccda->patient, $this->ccda, $this->enrollee);

        return $this;
    }

    /**
     * Store Phone Numbers.
     *
     * @return $this
     */
    private function importPhones()
    {
        ImportPhones::for($this->ccda->patient, $this->ccda, $this->enrollee);

        return $this;
    }

    private function importPractice()
    {
        AttachPractice::for($this->ccda->patient, $this->ccda);

        return $this;
    }

    /**
     * Store ProblemImports as Problem Models.
     *
     * @return $this
     */
    private function importProblems()
    {
        ImportProblems::for($this->ccda->patient, $this->ccda);

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
        ImportVitals::for($this->ccda->patient, $this->ccda);

        return $this;
    }

    private function isFamily(string $email, object $demographics, Enrollee $enrollee)
    {
        $phones = collect($demographics->phones)
            ->pluck('number')
            ->map(fn ($num) => formatPhoneNumberE164($num))
            ->merge([
                formatPhoneNumberE164($enrollee->primary_phone),
                formatPhoneNumberE164($enrollee->cell_phone),
                formatPhoneNumberE164($enrollee->home_phone),
                formatPhoneNumberE164($enrollee->other_phone),
            ])
            ->unique()
            ->filter()
            ->all();

        if (empty($phones)) {
            return false;
        }

        return User::ofType(['participant', 'survey-only'])
            ->where('email', $email)
            ->where('first_name', '!=', $this->ccda->patient_first_name)
            ->where(function ($q) use ($phones, $demographics) {
                $transDems = (new CcdToLogTranformer())->demographics($demographics);

                $q->whereHas('phoneNumbers', function ($q) use ($phones) {
                    $q->whereIn('number', $phones);
                })->orWhere('last_name', '=', $this->ccda->patient_last_name)
                ->when( ! empty($transDems), function ($q) use ($transDems) {
                    $q->orWhere([
                        ['address', '=', $transDems['street']],
                        ['address2', '=', $transDems['street2']],
                        ['city', '=', $transDems['city']],
                        ['state', '=', $transDems['state']],
                        ['zip', '=', $transDems['zip']],
                    ]);
                    });
            })
            ->exists();
    }

    private function patientEmail()
    {
        $email = $this->ccda->patient_email;

        if (empty($email) || in_array(strtolower($email), ['noemail@noemail.com', 'null'])) {
            return null;
        }

        return $email;
    }

    private function raiseConcernsOrAutoQAApprove()
    {
        $this->ccda->load(['patient.carePlan']);

        if ( ! $this->ccda->patient || ! $this->ccda->patient->carePlan) {
            return $this;
        }

        $validator                     = $this->ccda->patient->carePlan->validator();
        $this->ccda->validation_checks = null;
        if ($validator->fails()) {
            $this->ccda->validation_checks = $validator->errors();

            return $this;
        }

        if (CarePlan::DRAFT === $this->ccda->patient->carePlan->status && CarePlanAutoApprover::id()) {
            $this->ccda->patient->carePlan->status         = CarePlan::QA_APPROVED;
            $this->ccda->patient->carePlan->qa_approver_id = CarePlanAutoApprover::id();
            $this->ccda->patient->carePlan->qa_date        = now()->toDateTimeString();
            $this->ccda->patient->carePlan->save();

            $this->ccda->patient->patientInfo->ccm_status = Patient::ENROLLED;
            $this->ccda->patient->patientInfo->save();
        }

        return $this;
    }

    private function throwExceptionIfSuspicious(Enrollee $enrollee)
    {
        if (strtolower($this->ccda->patient->last_name) != strtolower($enrollee->last_name)) {
            throw new \Exception("
            Something mucho fishy is going on.
            Enrollee {$enrollee->id} has user {$enrollee->user_id},
            which does not have the same `Last Name` as user:{$this->ccda->patient->id}.");
        }

        if (strtolower($this->ccda->patient->first_name) === strtolower($enrollee->first_name)) {
            return;
        }

        if ($enrollee->user_id && ($enrollee->user_id !== $this->ccda->patient->id)) {
            throw new \Exception("
            Something no bueno is going on.
            Enrollee {$enrollee->id} has user {$enrollee->user_id},
            and now we are trying to attach user {$this->ccda->patient->id}.
            ");
        }

        //Both names are the same, but one includes a middle name
        if (self::isSameNameButOneHasMiddleInitial($this->ccda->patient->first_name, $enrollee->first_name)) {
            return;
        }

        throw new \Exception("
        Something fishy of undefined proportions is going on friend.
        You have unleashed the anger of the Gods by reaching the unreachable code block.
        Enrollee {$enrollee->id} has User {$enrollee->user_id},
        and now we are trying to attach user {$this->ccda->patient->id}.
        ");
    }

    private function updateAddressesFromCareAmbassadorInput()
    {
        //Care Ambassador can confirm/change mailing and email addresses from consented modal

        //do not update if enrollee does not have care ambassador
        if (empty($this->enrollee->care_ambassador_user_id)) {
            return $this;
        }

        if ($this->ccda->patient->email !== $this->enrollee->email) {
            $this->ccda->patient->email = $this->enrollee->email;
        }

        if ($this->ccda->patient->address !== $this->enrollee->address) {
            $this->ccda->patient->address = $this->enrollee->address;
        }

        if ($this->ccda->patient->address2 !== $this->enrollee->address_2) {
            $this->ccda->patient->address2 = $this->enrollee->address_2;
        }

        if ($this->ccda->patient->state !== $this->enrollee->state) {
            $this->ccda->patient->state = $this->enrollee->state;
        }

        if ($this->ccda->patient->city !== $this->enrollee->city) {
            $this->ccda->patient->city = $this->enrollee->city;
        }

        if ($this->ccda->patient->zip !== $this->enrollee->zip) {
            $this->ccda->patient->zip = $this->enrollee->zip;
        }

        //no need to save - these will be saved at updatePatientUserPostImport if changes did exist

        return $this;
    }

    private function updateCcdaPostImport()
    {
        //This CarePlan is now ready to be QA'ed by a CLH Admin
        $this->ccda->imported = true;
        if (in_array($this->ccda->patient->carePlan->status, [CarePlan::QA_APPROVED, CarePlan::RN_APPROVED, CarePlan::PROVIDER_APPROVED])) {
            $this->ccda->status = Ccda::CAREPLAN_CREATED;
        } else {
            $this->ccda->status = Ccda::QA;
        }
        if ( ! $this->ccda->mrn) {
            $this->ccda->mrn = $this->ccda->patient->patientInfo->mrn_number;
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
        $this->enrollee->status              = Enrollee::ENROLLED;
        if ($this->enrollee->isDirty()) {
            $this->enrollee->save();
        }

        return $this;
    }

    private function updatePatientUserPostImport()
    {
        if ($this->ccda->patient->isDirty()) {
            $this->ccda->patient->save();
        }

        return $this;
    }
}
