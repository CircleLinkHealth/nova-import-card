<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use Carbon\Carbon;
use CircleLinkHealth\Core\Tests\TestCase;
use CircleLinkHealth\Customer\DTO\PostmarkCallbackInboundData;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientNurse;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Jobs\ProcessPostmarkInboundMailJob;
use CircleLinkHealth\Customer\Services\Postmark\PostmarkInboundCallbackMatchResults;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundCallbackRequest;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundMail;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundMailRequest;
use CircleLinkHealth\SharedModels\Entities\UnresolvedPostmarkCallback;
use CircleLinkHealth\SharedModels\Notifications\CallCreated;
use CircleLinkHealth\SharedModels\Services\SchedulerService;
use CircleLinkHealth\SharedModels\Traits\Tests\PostmarkCallbackHelpers;
use Notification;

class AutoAssignCallbackTest extends TestCase
{
    use PostmarkCallbackHelpers;
    use PracticeHelpers;
    use UserHelpers;

    private User $careAmbassador;
    private User $patient;
    private Enrollee $patientEnrollee;
    private $phone;
    private PostmarkInboundMail $postmarkRecord;
    private Practice $practice;
    private User $standByNurse;

    public function setUp(): void
    {
        parent::setUp();
        $this->practice       = $this->practiceForSeeding();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
    }

    public function test_email_body_is_decoded_successfully()
    {
        $patient         = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
        $postmarkRecord  = $this->createPostmarkCallbackData(false, false, $patient);
        $inboundTextBody = collect(json_decode($postmarkRecord->data))->toArray();

        assert(isset($inboundTextBody['TextBody']));

        $textBodyData     = $inboundTextBody['TextBody'];
        $inboundData      = (new PostmarkInboundCallbackRequest())->process($textBodyData, $postmarkRecord->id);
        $inboundDataArray = $inboundData->toArray();

        assert( ! empty($inboundDataArray));
        assert( ! isset($inboundDataArray[PostmarkCallbackInboundData::CANCELLATION_REASON_KEY]));
        $keys = (new PostmarkInboundCallbackRequest())->getKeys();

        foreach ($keys as $key) {
            $keyTrimmed = trim($key, ':');
            if (isset($inboundDataArray[$keyTrimmed])) {
                assert(array_key_exists($keyTrimmed, $inboundDataArray));
            }
        }
    }

    public function test_email_body_with_extra_keys_is_decoded_successfully()
    {
        $patient         = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
        $postmarkRecord  = $this->createPostmarkCallbackData(true, false, $patient);
        $inboundTextBody = collect(json_decode($postmarkRecord->data))->toArray();

        assert(isset($inboundTextBody['TextBody']));

        $textBodyData     = $inboundTextBody['TextBody'];
        $inboundData      = (new PostmarkInboundCallbackRequest())->process($textBodyData, $postmarkRecord->id);
        $inboundDataArray = $inboundData->toArray();
        $rawInboundData   = $inboundData->rawInboundCallbackData();

        assert(isset($inboundDataArray[PostmarkCallbackInboundData::CANCELLATION_FORMATTED_KEY]));
        assert(isset($rawInboundData[PostmarkCallbackInboundData::CANCELLATION_REASON_KEY]));

        $keys = (new PostmarkInboundCallbackRequest())->getKeys();

        foreach ($keys as $key) {
            asset(isset($rawInboundData[$key]));
        }
    }

    /*
     * Does not work because it uses Enrollees::searchPhones() and this requires a search in DB. However our tests don't actually save in DB.
    public function test_enrollee_requests_callback_without_user_model()
    {
        $faker                     = Factory::create();
        $fakePatient               = new User();
        $fakePatient->id           = null;
        $fakePatient->first_name   = $faker->firstName;
        $fakePatient->last_name    = $faker->lastName;
        $fakePatient->display_name = $fakePatient->first_name.' '.$fakePatient->last_name;
        $phone                     = new PhoneNumber();
        $phone->number             = (new StringManipulation())->formatPhoneNumberE164($faker->phoneNumber);
        $fakePatient->setRelation('phoneNumbers', collect([$phone]));
        $this->createEnrolleeWithStatus($fakePatient, $this->careAmbassador->id, Enrollee::CONSENTED, $this->practice->id);
        $postmarkRecord = $this->createPostmarkCallbackData(false, true, $fakePatient);
        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $postmarkRecord->id,
        ]);
    }
    */

    public function test_if_existing_callback_exists_it_will_upate_it_and_mark_as_asap()
    {
        $patient = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
        $nurse   = $this->createUser(Practice::firstOrFail()->id, 'care-center');
        $this->setUpPermanentNurse($nurse, $patient);

        Call::create([
            'type'            => SchedulerService::TASK_TYPE,
            'status'          => Call::SCHEDULED,
            'inbound_cpm_id'  => $patient->id,
            'outbound_cpm_id' => $nurse->id,
            'scheduler'       => $nurse->id,
            'sub_type'        => SchedulerService::CALL_BACK_TYPE,
        ]);

        $postmarkRecord = $this->createPostmarkCallbackData(false, false, $patient);
        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        $this->assertCallbackExists($patient->id, $nurse->id);
    }

    public function test_if_name_is_self_and_patient_is_enrolled_it_will_create_callback()
    {
        $patient = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
        $nurse   = $this->createUser(Practice::firstOrFail()->id, 'care-center');
        $this->setUpPermanentNurse($nurse, $patient);
        $postmarkRecord = $this->createPostmarkCallbackData(false, true, $patient);
        /** @var PhoneNumber $phone1 */
        $phone1   = $this->phone;
        $patient2 = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
        $patient2->phoneNumbers
            ->first()
            ->update(
                [
                    'number' => $phone1->number,
                ]
            );
        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        $this->assertCallbackExists($patient->id, $nurse->id);
    }

    public function test_it_saves_as_unresolved_callback_if_patient_consented_but_not_enrolled()
    {
        $patient = $this->createPatientData(Patient::TO_ENROLL, $this->practice->id, Enrollee::CONSENTED, 'participant');
        $this->createEnrolleeWithStatus($patient, $this->careAmbassador->id, $this->practice->id, Enrollee::CONSENTED);
        $postmarkRecord = $this->createPostmarkCallbackData(false, true, $patient);
        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        $this->assertMissingCallBack($patient->id);
        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $postmarkRecord->id,
        ]);

        $this->assertUnresolvedReason(PostmarkInboundCallbackMatchResults::NOT_ENROLLED, $postmarkRecord->id);
    }

    public function test_it_saves_as_unresolved_if_multiple_patients_matched_and_have_same_number_and_name()
    {
        $patient1        = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
        $postmarkRecord1 = $this->createPostmarkCallbackData(false, false, $patient1);

        /** @var PhoneNumber $phone1 */
        $phone1 = $this->phone;

        $patient2 = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');

        $patient2->phoneNumbers
            ->first()
            ->update(
                [
                    'number' => $phone1->number,
                ]
            );

        $patient2->first_name   = $patient1->first_name;
        $patient2->last_name    = $patient1->last_name;
        $patient2->display_name = $patient1->first_name.' '.$patient1->last_name;
        $patient2->save();
        $patient2->fresh();
        $this->createPostmarkCallbackData(false, false, $patient2);

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord1->data))->toArray(), $postmarkRecord1->id);
        $this->assertMissingCallBack($patient1->id);
        $this->assertMissingCallBack($patient2->id);
        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $postmarkRecord1->id,
        ]);
    }

    public function test_it_saves_as_unresolved_if_patient_is_auto_enroll_and_has_not_got_care_ambassador()
    {
        $patient        = $this->createPatientData(Patient::TO_ENROLL, $this->practice->id, Enrollee::QUEUE_AUTO_ENROLLMENT, 'survey-only');
        $postmarkRecord = $postmarkRecord = $this->createPostmarkCallbackData(false, false, $patient);

        $patient->enrollee->update(
            [
                'care_ambassador_user_id' => null,
            ]
        );

        $patient->enrollee->save();

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);

        $this->assertMissingCallBack($patient->id);

        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $postmarkRecord->id,
        ]);

        $this->assertUnresolvedReason(PostmarkInboundCallbackMatchResults::QUEUED_AND_UNASSIGNED, $postmarkRecord->id);
    }

    public function test_it_saves_as_unresolved_if_patient_not_consented_and_has_no_care_ambassador_id()
    {
        $patient         = $this->createPatientData(Patient::TO_ENROLL, $this->practice->id, Enrollee::ELIGIBLE, 'participant');
        $postmarkRecord1 = $this->createPostmarkCallbackData(false, false, $patient);
        $patient->enrollee->update(
            [
                'care_ambassador_user_id' => null,
            ]
        );

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord1->data))->toArray(), $postmarkRecord1->id);
        $this->assertUnresolvedReason(PostmarkInboundCallbackMatchResults::NOT_CONSENTED_CA_UNASSIGNED, $postmarkRecord1->id);
        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $postmarkRecord1->id,
        ]);
    }

    public function test_it_saves_as_unresolved_if_patient_requested_to_withdraw()
    {
        $patient        = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
        $postmarkRecord = $this->createPostmarkCallbackData(true, false, $patient);
        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        $this->assertMissingCallBack($patient->id);
        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $postmarkRecord->id,
        ]);

        $this->assertUnresolvedReason(PostmarkInboundCallbackMatchResults::WITHDRAW_REQUEST, $postmarkRecord->id);
    }

    public function test_it_wil_sanitize_phone_with_correct_format_and_create_callback()
    {
        $phoneFormatsCases = [
            '527-931-9827',
            '5279319827',
            '5279319827 5279319827',
        ];

        foreach ($phoneFormatsCases as $phoneFormat) {
            $patient = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
            $nurse   = $this->createUser(Practice::firstOrFail()->id, 'care-center');
            $this->setUpPermanentNurse($nurse, $patient);
            $patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => '+15279319827',
                    ]
                );
            $patient->fresh();

            $postmarkRecord = $this->createPostmarkCallbackData(false, false, $patient, $phoneFormat);

            $patient->phoneNumbers->fresh();
            $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
            $this->assertCallbackExists($patient->id, $nurse->id);
        }
    }

    public function test_it_will_assign_to_care_ambassador_if_patient_not_consented_and_has_care_ambassador_id()
    {
        $patient        = $this->createPatientData(Patient::TO_ENROLL, $this->practice->id, Enrollee::ELIGIBLE, 'participant');
        $postmarkRecord = $this->createPostmarkCallbackData(false, false, $patient);
        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        $this->assertDatabaseHas('enrollees', [
            'id'                      => $patient->enrollee->id,
            'status'                  => Enrollee::TO_CALL,
            'care_ambassador_user_id' => $patient->enrollee->care_ambassador_user_id,
            'requested_callback'      => Carbon::now()->toDateString(),
            'callback_note'           => 'Callback automatically scheduled by the system - patient requested callback',
        ]);
    }

    public function test_it_will_transform_non_accepted_phones_formats_and_create_callback()
    {
        $phoneFormatsCaseA = [
            '527-931-9827',
            '§527-931-9827',
            '527-931-9827 Marios PIkatilis',
        ];

        foreach ($phoneFormatsCaseA as $phoneFormat) {
            $patient = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
            $nurse   = $this->createUser(Practice::firstOrFail()->id, 'care-center');
            $this->setUpPermanentNurse($nurse, $patient);
            $patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => '527-931-9827',
                    ]
                );
            $patient->fresh();

            $postmarkRecord = $this->createPostmarkCallbackData(false, false, $patient, $phoneFormat);

            $patient->phoneNumbers->fresh();
            $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
            $this->assertCallbackExists($patient->id, $nurse->id);
        }
    }

    public function test_scrabled_input_data_will_be_sanitized_and_create_callback()
    {
        $patient = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
        $nurse   = $this->createUser(Practice::firstOrFail()->id, 'care-center');
        $this->setUpPermanentNurse($nurse, $patient);
        $postmarkRecord = $this->createPostmarkCallbackData(false, false, $patient, '', true);

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        $this->assertCallbackExists($patient->id, $nurse->id);
    }

    public function test_when_callback_is_created_assigned_nurse_will_get_live_notification()
    {
        Notification::fake();
        $patient = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
        $nurse   = $this->createUser(Practice::firstOrFail()->id, 'care-center');
        $this->setUpPermanentNurse($nurse, $patient);
        $postmarkRecord = $this->createPostmarkCallbackData(false, true, $patient);
        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        Notification::assertSentTo($nurse, CallCreated::class);
    }

    public function test_when_multiple_users_matched_by_number_will_resolve_to_one_user_and_create_callback_if_enrolled()
    {
        $patient1 = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
        $nurse    = $this->createUser(Practice::firstOrFail()->id, 'care-center');
        $this->setUpPermanentNurse($nurse, $patient1);
        $postmarkRecord = $this->createPostmarkCallbackData(false, false, $patient1);

        /** @var PhoneNumber $phone */
        $phone = $this->phone;

        $patient2 = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED, 'participant');
        $patient2->phoneNumbers
            ->first()
            ->update(
                [
                    'number' => $phone->number,
                ]
            );

        $patient2->phoneNumbers->fresh();

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        $this->assertCallbackExists($patient1->id, $nurse->id);
    }

    private function assertCallbackExists(int $patientId, int $nurseId)
    {
        $this->assertDatabaseHas('calls', [
            'inbound_cpm_id' => $patientId,
            'sub_type'       => SchedulerService::CALL_BACK_TYPE,
            'scheduler'      => $nurseId,
            'asap'           => true,
            'status'         => Call::SCHEDULED,
        ]);
    }

    private function assertMissingCallBack(int $patientId)
    {
        $this->assertDatabaseMissing('calls', [
            'inbound_cpm_id' => $patientId,
            'sub_type'       => SchedulerService::CALL_BACK_TYPE,
        ]);
    }

    private function assertUnresolvedReason(string $reason, int $postmarkRecordId)
    {
        $unresolvedPostmarkRecord = UnresolvedPostmarkCallback::where('postmark_id', $postmarkRecordId)->first();

        assert( ! is_null($unresolvedPostmarkRecord));

        assert($unresolvedPostmarkRecord->unresolved_reason === $reason);
    }

    private function dispatchPostmarkInboundMail(array $recordData, int $recordId)
    {
        ProcessPostmarkInboundMailJob::dispatchNow(
            new PostmarkInboundMailRequest($recordData),
            $recordId
        );
    }

    private function setUpPermanentNurse(User $nurse, User $patient)
    {
        PatientNurse::create([
            'patient_user_id' => $patient->id,
            'nurse_user_id'   => $nurse->id,
        ]);
    }
}
