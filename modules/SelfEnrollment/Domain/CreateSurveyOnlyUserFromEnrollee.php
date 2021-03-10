<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Domain;

use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Exceptions\PatientAlreadyExistsException;
use CircleLinkHealth\Customer\Repositories\UserRepository;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporter;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\DTO\Address;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\ParameterBag;

class CreateSurveyOnlyUserFromEnrollee
{
    protected Enrollee $enrollee;

    /**
     * @var \Illuminate\Database\Eloquent\Model|Role
     */
    private $surveyRole;

    public function __construct(Enrollee $enrollee)
    {
        $this->enrollee = $enrollee;
    }

    public function create()
    {
        $count            = 0;
        $this->surveyRole = $this->surveyRole();

        // UNREACHABLE_PATIENT marks unreachable patients in enrollee model. Doesnt get updated.
        if ( ! empty($this->enrollee->user_id) && Enrollee::UNREACHABLE_PATIENT === $this->enrollee->source) {
            Log::warning("Enrollee with id [$this->enrollee->id] is unreachable patient. Should not be here");

            return;
        }

        // If Enrollee and invited again (has user_id) the dont create user. Just invite
        if ( ! empty($this->enrollee->user_id) && Enrollee::QUEUE_AUTO_ENROLLMENT === $this->enrollee->status) {
            return;
        }

        // If any other reason then something is wrong
        if ( ! empty($this->enrollee->user_id)) {
            Log::critical("Enrollee with id [{$this->enrollee->id}] should not have reached this point");

            return;
        }

        $email = self::sanitizeEmail($this->enrollee->id, $this->enrollee->email);

        if ( ! $this->enrollee->provider_id && $this->enrollee->referring_provider_name) {
            $this->enrollee->provider_id = optional(CcdaImporterWrapper::searchBillingProvider($this->enrollee->referring_provider_name, $this->enrollee->practice_id))->id;
        }

        //Naive way to validate it will not break in UserRepository when creating the User and halt sendng the auto-enrollment invites
        if ( ! $email || ! $this->enrollee->practice_id || ! $this->enrollee->provider_id || ! $this->enrollee->first_name || ! $this->enrollee->last_name || ! $this->enrollee->dob || ! $this->enrollee->mrn) {
            return;
        }

        try {
            $this->enrollee->fresh();
            UserRepository::validatePatientDoesNotAlreadyExist(
                $this->enrollee->practice_id,
                $this->enrollee->first_name,
                $this->enrollee->last_name,
                $this->enrollee->dob,
                $this->enrollee->mrn
            );
        } catch (PatientAlreadyExistsException $e) {
            $this->enrollee->user_id = $e->getPatientUserId();
            $this->enrollee->save();

            return;
        }

        $ccda  = $this->enrollee->ccda;
        $isAwv = false;
        if ($ccda) {
            $ccda->billing_provider_id = $this->enrollee->provider_id;
            $isAwv                     = Ccda::IMPORTER_AWV === $ccda->source;
        }

        $phones = array_filter([
            (new StringManipulation())->formatPhoneNumberE164($this->enrollee->primary_phone),
            (new StringManipulation())->formatPhoneNumberE164($this->enrollee->cell_phone),
            (new StringManipulation())->formatPhoneNumberE164($this->enrollee->home_phone),
            (new StringManipulation())->formatPhoneNumberE164($this->enrollee->other_phone),
        ]);

        $address = new Address($this->enrollee->address, $this->enrollee->city, $this->enrollee->state, $this->enrollee->zip, $this->enrollee->address_2);

        if (CcdaImporter::isFamily($email, $phones, $this->enrollee->first_name, $this->enrollee->last_name, $address)) {
            $email = CcdaImporter::convertToFamilyEmail($email);
        }

        if (CcdaImporter::emailIsTaken($email, $this->enrollee->first_name, $this->enrollee->last_name)) {
            $email = CcdaImporter::EMAIL_EXISTS_BUT_NOT_FAMILY_PREFIX.$email;
        }

        $userCreatedFromEnrollee = (new UserRepository())->createNewUser(
            new ParameterBag(
                [
                    'email'        => $email,
                    'password'     => Str::random(30),
                    'display_name' => ucwords(
                        strtolower($this->enrollee->first_name.' '.$this->enrollee->last_name)
                    ),
                    'first_name'        => $this->enrollee->first_name,
                    'last_name'         => $this->enrollee->last_name,
                    'mrn_number'        => $this->enrollee->mrn,
                    'birth_date'        => $this->enrollee->dob,
                    'username'          => $email,
                    'program_id'        => $this->enrollee->practice_id,
                    'is_auto_generated' => true,
                    'roles'             => [$this->surveyRole->id],
                    'is_awv'            => $isAwv,
                    'address'           => $this->enrollee->address,
                    'address2'          => $this->enrollee->address_2,
                    'city'              => $this->enrollee->city,
                    'state'             => $this->enrollee->state,
                    'zip'               => $this->enrollee->zip,

                    //Important requirement for all "Self Enrollment" workflows.
                    'ccm_status' => Patient::UNREACHABLE,
                ]
            )
        );

        if ($ccda) {
            $ccda->patient_id = $userCreatedFromEnrollee->id;
            $ccda->save();
        }

        $this->attachPhones($userCreatedFromEnrollee, $this->enrollee);

        if ($this->enrollee->provider_id) {
            $id         = $this->enrollee->id;
            $providerId = $this->enrollee->provider_id;
            Log::debug("Setting provider for enrollee[$id]: $providerId");
            $userCreatedFromEnrollee->setBillingProviderId($this->enrollee->provider_id);
        } else {
            Log::debug('provider_id not found. Will not set.');
        }

        $this->enrollee->update(
            [
                'user_id' => $userCreatedFromEnrollee->id,
            ]
        );
    }

    public static function execute(Enrollee $enrollee)
    {
        (new static($enrollee))->create();
    }

    public static function fakeCpmFillerEmail(int $id)
    {
        return "e{$id}@careplanmanager.com";
    }

    public static function nullEmailValues()
    {
        return [
            'noemail@noemail.com',
            'null',
            'none',
            'none@none.com',
            'n/a',
            '123@yahoo.com',
            '1234@yahoo.com',
            'donthaveone@yahoo.com',
            'unknown@unknown.com',
        ];
    }

    public static function sanitizeEmail(int $id, ?string $email): ?string
    {
        $email = str_replace(' ', '', $email);

        if (Validator::make([
            'email' => $email,
        ], [
            'email' => [
                'required',
                'filled',
                'email',
                Rule::notIn(self::nullEmailValues()),
            ],
        ])->fails()) {
            return self::fakeCpmFillerEmail($id);
        }

        return $email;
    }

    private function attachPhones(User $userCreatedFromEnrollee, Enrollee $enrollee)
    {
        $cellPhone = $enrollee->cell_phone_e164;
        if ( ! empty($cellPhone)) {
            $userCreatedFromEnrollee->phoneNumbers()->create([
                'type'       => PhoneNumber::MOBILE,
                'number'     => $cellPhone,
                'is_primary' => true,
            ]);
        }

        $primaryPhone = $enrollee->primary_phone_e164;
        if ($primaryPhone) {
            $userCreatedFromEnrollee->phoneNumbers()->create([
                'type'       => PhoneNumber::HOME,
                'number'     => $primaryPhone,
                'is_primary' => false,
            ]);
        }

        $homePhone = $enrollee->home_phone_e164;
        if ($homePhone) {
            $userCreatedFromEnrollee->phoneNumbers()->create([
                'type'       => PhoneNumber::HOME,
                'number'     => $homePhone,
                'is_primary' => false,
            ]);
        }

        $otherPhone = $enrollee->other_phone_e164;
        if ($otherPhone) {
            $userCreatedFromEnrollee->phoneNumbers()->create([
                'number'     => $otherPhone,
                'is_primary' => false,
            ]);
        }
    }

    private function surveyRole(): Role
    {
        $this->surveyRole = Role::firstOrCreate(
            [
                'name' => 'survey-only',
            ],
            [
                'display_name' => 'Survey User',
                'description'  => 'Became Users just to be enrolled in AWV survey',
            ]
        );

        return $this->surveyRole;
    }
}
