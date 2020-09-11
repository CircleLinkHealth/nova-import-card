<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Jobs\ProcessPostmarkInboundMailJob;
use App\PostmarkInboundMail;
use App\Services\Calls\SchedulerService;
use App\Traits\Tests\PostmarkCallbackHelpers;
use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\AppConfig\StandByNurseUser;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
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
        $this->practice       = $this->setupPractice();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
        $this->standByNurse   = $this->createUser($this->practice->id, 'care-center');
        AppConfig::create(
            [
                'config_key'   => StandByNurseUser::STAND_BY_NURSE_USER_ID_NOVA_KEY,
                'config_value' => $this->standByNurse->id,
            ]
        );
    }

    public function test_it_assign_to_ops_if_name_is_self_and_multiple_match_is_not_resolved()
    {
        $this->createPatientData(Enrollee::ENROLLED, '', true);

        $patient1        = $this->patient;
        $postmarkRecord1 = $this->postmarkRecord;
        $phone1          = $this->phone;

        $this->createPatientData(Enrollee::ENROLLED);

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
    }

    public function test_it_will_assign_to_ops_if_patient_is_auto_enroll_and_has_unassigned_care_ambassador()
    {
        $this->createPatientData(Enrollee::QUEUE_AUTO_ENROLLMENT);

        $this->patientEnrollee->update(
            [
                'care_ambassador_user_id' => null,
            ]
        );

        $this->dispatchPostmarkInboundMail(collect(json_decode($this->postmarkRecord->data))->toArray(), $this->postmarkRecord->id);

        $this->assertMissingCallBack($this->patient->id);

//        @todo:test_it_will_assign_to_ops
    }

    public function test_it_will_assign_to_ops_if_patient_is_not_enrolled()
    {
        $this->createPatientData(Patient::PAUSED);
        $this->dispatchPostmarkInboundMail(collect(json_decode($this->postmarkRecord->data))->toArray(), $this->postmarkRecord->id);

        $this->assertMissingCallBack($this->patient->id);
        //        @todo:test_it_will_assign_to_ops
    }

    public function test_it_will_assign_to_ops_if_patient_requested_to_withdraw()
    {
        $this->createPatientData(Patient::ENROLLED, true);
        $this->dispatchPostmarkInboundMail(collect(json_decode($this->postmarkRecord->data))->toArray(), $this->postmarkRecord->id);
        $this->assertMissingCallBack($this->patient->id);
        //        @todo:test_it_will_assign_to_ops
    }

    public function test_it_will_assign_to_ops_if_unresolved_multi_match_patients()
    {
        $this->createPatientData(Enrollee::ENROLLED);

        $patient1         = $this->patient;
        $patientEnrollee1 = $this->patientEnrollee;
        $postmarkRecord1  = $this->postmarkRecord;
        $phone1           = $this->phone;

        $this->createPatientData(Enrollee::ENROLLED);

        $patient2         = $this->patient;
        $patientEnrollee2 = $this->patientEnrollee;
        $postmarkRecord2  = $this->postmarkRecord;
        $phone2           = $this->phone;

        $patient2->phoneNumbers
            ->first()
            ->update(
                [
                    'number' => $phone1->number,
                ]
            );

        $patient2->phoneNumbers->fresh();
        $patient1->display_name = $patient2->display_name;
        $patient1->save();
        $patient1->fresh();

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

        $this->assertDatabaseHas('postmark_inbound_mail', [
            'id' => $postmarkRecord2->id,
        ]);

        assert($patient1->display_name === $patient2->display_name);

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord1->data))->toArray(), $postmarkRecord1->id);

        $this->assertMissingCallBack($patient1->id);

        $this->assertMissingCallBack($patient2->id);
    }

    public function test_it_will_create_callback_if_multiple_match_is_resolved()
    {
        $this->createPatientData(Enrollee::ENROLLED);
        $patient1        = $this->patient;
        $postmarkRecord1 = $this->postmarkRecord;

        $this->createPatientData(Enrollee::ENROLLED);
        $patient2 = $this->patient;

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord1->data))->toArray(), $postmarkRecord1->id);
        $this->assertCallbackExists($patient1->id);
        $this->assertMissingCallBack($patient2->id);
    }

    public function test_it_will_create_callback_when_name_is_self_and_resolved_to_single_match()
    {
        $this->createPatientData(Enrollee::ENROLLED, false, true);
        $patient1        = $this->patient;
        $postmarkRecord1 = $this->postmarkRecord;
        $phone1          = $this->phone;

        $this->createPatientData(Enrollee::ENROLLED);
        $patient2 = $this->patient;
        $patient2->phoneNumbers
            ->first()
            ->update(
                [
                    'number' => $phone1->number,
                ]
            );

        $patient2->phoneNumbers->fresh();
        $phone2 = $phone1;

        $this->dispatchPostmarkInboundMail(collect(json_decode($postmarkRecord1->data))->toArray(), $postmarkRecord1->id);
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

    private function dispatchPostmarkInboundMail(array $recordData, int $recordId)
    {
        ProcessPostmarkInboundMailJob::dispatchNow(
            $recordData,
            $recordId
        );
    }
}
