<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Entities\PostmarkInboundCallbackRequest;
use App\Entities\PostmarkInboundMailRequest;
use App\Jobs\ProcessPostmarkInboundMailJob;
use App\Notifications\CallCreated;
use App\PostmarkInboundMail;
use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Customer\Services\Postmark\PostmarkInboundCallbackMatchResults;
use App\Traits\Tests\PostmarkCallbackHelpers;
use App\UnresolvedPostmarkCallback;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientNurse;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Notification;
use Tests\TestCase;

class AutoAssignCallbackTest extends TestCase
{
    use PostmarkCallbackHelpers;
    use PracticeHelpers;
    use UserHelpers;

    /**
     * @var User
     */
    private $careAmbassador;
    /**
     * @var User
     */
    private $patient;
    /**
     * @var Enrollee|\Illuminate\Database\Eloquent\Model
     */
    private $patientEnrollee;
    /**
     * @var string
     */
    private $phone;
    /**
     * @var \Illuminate\Database\Eloquent\Model|PostmarkInboundMail
     */
    private $postmarkRecord;
    /**
     * @var \CircleLinkHealth\Customer\Entities\Practice
     */
    private $practice;

    private User $standByNurse;

    public function setUp(): void
    {
        parent::setUp();
        $this->practice       = $this->practiceForSeeding();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
    }

    public function test_email_body_is_decoded_successfully()
    {
        $patient         = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED);
        $postmarkRecord  = $this->createPostmarkCallbackData(false, false, $patient);
        $inboundTextBody = collect(json_decode($postmarkRecord->data))->toArray();

        assert(isset($inboundTextBody['TextBody']));

        $textBodyData     = $inboundTextBody['TextBody'];
        $inboundDataArray = (new PostmarkInboundCallbackRequest())->run($textBodyData, $postmarkRecord->id);

        assert(is_array($inboundDataArray));
        assert( ! isset($inboundDataArray['Cancel/Withdraw Reason']));
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
        $patient         = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED);
        $postmarkRecord  = $this->createPostmarkCallbackData(true, false, $patient);
        $patient         = $this->patient;
        $inboundTextBody = collect(json_decode($postmarkRecord->data))->toArray();

        assert(isset($inboundTextBody['TextBody']));

        $textBodyData     = $inboundTextBody['TextBody'];
        $inboundDataArray = (new PostmarkInboundCallbackRequest())->run($textBodyData, $postmarkRecord->id);

        assert(is_array($inboundDataArray));
        assert(isset($inboundDataArray['Cancel/Withdraw Reason']));

        $keys = (new PostmarkInboundCallbackRequest())->getKeys();

        foreach ($keys as $key) {
            $keyTrimmed = trim($key, ':');
            if (isset($inboundDataArray[$keyTrimmed])) {
                assert(array_key_exists($keyTrimmed, $inboundDataArray));
            }
        }
    }

    public function test_it_saves_as_unresolved_callback_if_patient_consented_but_not_enrolled()
    {
        $patient = $this->createPatientData(Patient::TO_ENROLL, $this->practice->id, Enrollee::CONSENTED);
        $this->createEnrolleeData(Enrollee::CONSENTED, $patient, $this->practice->id, $this->careAmbassador->id);
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
        $patient1        = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED);
        $postmarkRecord1 = $this->createPostmarkCallbackData(false, true, $patient1);

        /** @var PhoneNumber $phone1 */
        $phone1 = $this->phone;

        $patient2 = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED);
        $patient2->phoneNumbers
            ->first()
            ->update(
                [
                    'number' => $phone1->number,
                ]
            );

        $patient2->first_name = $patient1->first_name;
        $patient2->last_name  = $patient1->last_name;
        $patient2->save();
        $patient2->fresh();
        $this->createPostmarkCallbackData(false, true, $patient2);

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord1->data))->toArray(), $postmarkRecord1->id);
        $this->assertMissingCallBack($patient1->id);
        $this->assertMissingCallBack($patient2->id);
        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $postmarkRecord1->id,
        ]);
    }

    public function test_it_saves_as_unresolved_if_patient_is_auto_enroll_and_has_not_got_care_ambassador()
    {
        $patient        = $this->createPatientData(Patient::TO_ENROLL, $this->practice->id, Enrollee::QUEUE_AUTO_ENROLLMENT);
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
        $patient         = $this->createPatientData(Patient::TO_ENROLL, $this->practice->id, Enrollee::ELIGIBLE);
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
        $patient        = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED);
        $postmarkRecord = $this->createPostmarkCallbackData(true, false, $patient);
        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        $this->assertMissingCallBack($patient->id);
        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $postmarkRecord->id,
        ]);

        $this->assertUnresolvedReason(PostmarkInboundCallbackMatchResults::WITHDRAW_REQUEST, $postmarkRecord->id);
    }

    public function test_it_will_assign_to_care_ambassador_if_patient_not_consented_and_has_care_ambassador_id()
    {
        $patient        = $this->createPatientData(Patient::TO_ENROLL, $this->practice->id, Enrollee::ELIGIBLE);
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

    public function test_when_callback_is_created_assigned_nurse_will_get_live_notification()
    {
        Notification::fake();
        $patient = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED);
        $nurse   = $this->createUser(Practice::firstOrFail()->id, 'care-center');
        $this->setUpPermanentNurse($nurse, $patient);
        $postmarkRecord = $this->createPostmarkCallbackData(false, true, $patient);
        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        Notification::assertSentTo($nurse, CallCreated::class);
    }

    public function test_when_multiple_users_matched_by_number_will_resolve_to_one_user_and_create_callback_if_enrolled()
    {
        $patient1 = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED);
        $nurse    = $this->createUser(Practice::firstOrFail()->id, 'care-center');
        $this->setUpPermanentNurse($nurse, $patient1);
        $postmarkRecord = $this->createPostmarkCallbackData(false, true, $patient1);

        /** @var PhoneNumber $phone */
        $phone = $this->phone;

        $patient2 = $this->createPatientData(Patient::ENROLLED, $this->practice->id, Enrollee::ENROLLED);
        $patient2->phoneNumbers
            ->first()
            ->update(
                [
                    'number' => $phone->number,
                ]
            );

        $patient2->phoneNumbers->fresh();

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord->data))->toArray(), $postmarkRecord->id);
        $this->assertCallbackExists($patient1->id);
    }

    private function assertCallbackExists(int $patientId)
    {
        $this->assertDatabaseHas('calls', [
            'inbound_cpm_id' => $patientId,
            'sub_type'       => SchedulerService::CALL_BACK_TYPE,
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
