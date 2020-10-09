<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Entities\PostmarkInboundCallbackRequest;
use App\Entities\PostmarkInboundMailRequest;
use App\Jobs\ProcessPostmarkInboundMailJob;
use App\PostmarkInboundMail;
use App\Services\Calls\SchedulerService;
use App\Services\Postmark\PostmarkInboundCallbackMatchResults;
use App\Traits\Tests\PostmarkCallbackHelpers;
use App\Traits\Tests\PracticeHelpers;
use App\UnresolvedPostmarkCallback;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\AppConfig\StandByNurseUser;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->practice       = $this->nekatostrasPractice();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
//        $this->standByNurse   = $this->createUser($this->practice->id, 'care-center');
//        AppConfig::create(
//            [
//                'config_key'   => StandByNurseUser::STAND_BY_NURSE_USER_ID_NOVA_KEY,
//                'config_value' => $this->standByNurse->id,
//            ]
//        );
    }

    public function test_a_multiple_match_resolved_to_single_match_with_name_self_will_create_callback()
    {
        $standByNurse = $this->createUser(Practice::firstOrFail()->id, 'care-center');

        AppConfig::create(
            [
                'config_key'   => StandByNurseUser::STAND_BY_NURSE_USER_ID_NOVA_KEY,
                'config_value' => $standByNurse->id,
            ]
        );

        $this->assertDatabaseHas('app_config', [
            'config_key'   => StandByNurseUser::STAND_BY_NURSE_USER_ID_NOVA_KEY,
            'config_value' => $standByNurse->id,
        ]);

        $this->createPatientData(Enrollee::ENROLLED);
        $this->createPostmarkCallbackData(false, true);
        $patient1 = $this->patient;
        /** @var PhoneNumber $phone */
        $phone = $this->phone;

        $this->createPatientData(Enrollee::ENROLLED);
        $patient2 = $this->patient;
        $patient2->phoneNumbers
            ->first()
            ->update(
                [
                    'number' => $phone->number,
                ]
            );

        $patient2->phoneNumbers->fresh();

        $this->dispatchPostmarkInboundMail(collect(json_decode($this->postmarkRecord->data))->toArray(), $this->postmarkRecord->id);
        $this->assertCallbackExists($patient1->id);
    }

    public function test_email_body_is_decoded_successfully()
    {
        $this->createPatientData(Enrollee::ENROLLED);
        $this->createPostmarkCallbackData(false, false);
        $patient         = $this->patient;
        $postmarkRecord  = $this->postmarkRecord;
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

    public function test_email_body_is_decoded_successfully_with_extra_keys()
    {
        $this->createPatientData(Enrollee::ENROLLED);
        $this->createPostmarkCallbackData(true, false);
        $patient         = $this->patient;
        $postmarkRecord  = $this->postmarkRecord;
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
        $this->createPatientData(Enrollee::CONSENTED);
        $this->createPostmarkCallbackData(false, true);
        $this->dispatchPostmarkInboundMail(collect(json_decode($this->postmarkRecord->data))->toArray(), $this->postmarkRecord->id);
        $this->assertMissingCallBack($this->patient->id);
        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $this->postmarkRecord->id,
        ]);

        $this->assertUnresolvedReason(PostmarkInboundCallbackMatchResults::NOT_ENROLLED);
    }

    public function test_it_saves_as_unresolved_if_its_unresolved_multi_match_patients()
    {
        $this->createPatientData(Enrollee::ENROLLED);
        $this->createPostmarkCallbackData(false, false);

        $patient1         = $this->patient;
        $patientEnrollee1 = $this->patientEnrollee;
        $postmarkRecord1  = $this->postmarkRecord;
        $phone1           = $this->phone;

        $this->createPatientData(Enrollee::ENROLLED);
        $this->createPostmarkCallbackData(false, false);
        $patient2         = $this->patient;
        $patientEnrollee2 = $this->patientEnrollee;

        $patient2->phoneNumbers
            ->first()
            ->update(
                [
                    'number' => $phone1->number,
                ]
            );

        $patient2->display_name = $patient1->display_name;
        $patient2->save();
        $patient2->fresh();

        $this->assertDatabaseHas('users', [
            'id' => $patient1->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $patient2->id,
        ]);

        $this->assertDatabaseHas('enrollees', [
            'id' => $patientEnrollee1->id,
        ]);

        $this->assertDatabaseHas('enrollees', [
            'id' => $patientEnrollee2->id,
        ]);

        $this->assertDatabaseHas('postmark_inbound_mail', [
            'id' => $postmarkRecord1->id,
        ]);

        assert($patient1->display_name === $patient2->display_name);

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord1->data))->toArray(), $postmarkRecord1->id);

        $this->assertMissingCallBack($patient1->id);

        $this->assertMissingCallBack($patient2->id);

        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $postmarkRecord1->id,
        ]);
    }

    public function test_it_saves_as_unresolved_if_name_is_self_and_multiple_match_is_not_resolved()
    {
        $this->createPatientData(Enrollee::ENROLLED);
        $this->createPostmarkCallbackData(false, true);
        $patient1        = $this->patient;
        $postmarkRecord1 = $this->postmarkRecord;
        /** @var PhoneNumber $phone1 */
        $phone1 = $this->phone;

        $this->createPatientData(Enrollee::ENROLLED);
        $this->createPostmarkCallbackData(false, true);
        $patient2 = $this->patient;

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

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord1->data))->toArray(), $postmarkRecord1->id);
        $this->assertMissingCallBack($patient1->id);
        $this->assertMissingCallBack($patient2->id);
        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $postmarkRecord1->id,
        ]);
    }

    public function test_it_saves_as_unresolved_if_patient_is_auto_enroll_and_has_unassigned_care_ambassador()
    {
        $this->createPatientData(Enrollee::QUEUE_AUTO_ENROLLMENT);
        $this->createPostmarkCallbackData(false, false);

        $this->patientEnrollee->update(
            [
                'care_ambassador_user_id' => null,
            ]
        );

        $this->patientEnrollee->save();

        $this->dispatchPostmarkInboundMail(collect(json_decode($this->postmarkRecord->data))->toArray(), $this->postmarkRecord->id);

        $this->assertMissingCallBack($this->patient->id);

        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $this->postmarkRecord->id,
        ]);

        $this->assertUnresolvedReason(PostmarkInboundCallbackMatchResults::QUEUED_AND_UNASSIGNED);
    }

    public function test_it_saves_as_unresolved_if_patient_not_consented_and_has_no_care_ambassador_id()
    {
        $this->createPatientData(Enrollee::ELIGIBLE);
        $this->createPostmarkCallbackData(false, false);
        $postmarkRecord1 = $this->postmarkRecord;

        $this->patientEnrollee->update(
            [
                'care_ambassador_user_id' => null,
            ]
        );

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord1->data))->toArray(), $postmarkRecord1->id);
        $this->assertUnresolvedReason(PostmarkInboundCallbackMatchResults::NOT_CONSENTED_CA_UNASSIGNED);
        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $this->postmarkRecord->id,
        ]);
    }

    public function test_it_saves_as_unresolved_if_patient_requested_to_withdraw()
    {
        $this->createPatientData(Patient::ENROLLED);
        $this->createPostmarkCallbackData(true, false);
        $this->dispatchPostmarkInboundMail(collect(json_decode($this->postmarkRecord->data))->toArray(), $this->postmarkRecord->id);
        $this->assertMissingCallBack($this->patient->id);
        $this->assertDatabaseHas('unresolved_postmark_callbacks', [
            'postmark_id' => $this->postmarkRecord->id,
        ]);

        $this->assertUnresolvedReason(PostmarkInboundCallbackMatchResults::WITHDRAW_REQUEST);
    }

    public function test_it_will_assign_to_care_ambassador_if_patient_not_consented_and_has_care_ambassador_id()
    {
        $this->createPatientData(Enrollee::ELIGIBLE);
        $this->createPostmarkCallbackData(false, false);

        $this->dispatchPostmarkInboundMail(collect(json_decode($this->postmarkRecord->data))->toArray(), $this->postmarkRecord->id);
        $this->assertUnresolvedReason(PostmarkInboundCallbackMatchResults::NOT_CONSENTED_CA_ASSIGNED);

        $this->assertDatabaseHas('enrollees', [
            'id'                      => $this->patientEnrollee->id,
            'status'                  => Enrollee::TO_CALL,
            'care_ambassador_user_id' => $this->patientEnrollee->care_ambassador_user_id,
            'requested_callback'      => Carbon::now()->toDateString(),
            'callback_note'           => 'Callback automatically scheduled by the system - patient requested callback',
        ]);
    }

    public function test_it_will_create_callback_if_multiple_match_is_resolved_to_single_match()
    {
        $this->createPatientData(Enrollee::ENROLLED);
        $this->createPostmarkCallbackData(false, false);
        $patient1        = $this->patient;
        $postmarkRecord1 = $this->postmarkRecord;

        $this->createPatientData(Enrollee::ENROLLED);
        $this->createPostmarkCallbackData(false, false);
        $patient2 = $this->patient;

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord1->data))->toArray(), $postmarkRecord1->id);
        $this->assertCallbackExists($patient1->id);
        $this->assertMissingCallBack($patient2->id);
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

    private function assertUnresolvedReason(string $reason)
    {
        $unresolvedPostmarkRecord = UnresolvedPostmarkCallback::where('postmark_id', $this->postmarkRecord->id)->first();

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
}
