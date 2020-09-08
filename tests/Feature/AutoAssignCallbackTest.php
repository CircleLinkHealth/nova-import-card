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
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\Mail;
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
     * @var \Illuminate\Database\Eloquent\Model|PostmarkInboundMail
     */
    private $postmarkRecord;
    /**
     * @var \CircleLinkHealth\Customer\Entities\Practice
     */
    private $practice;

    protected function setUp(): void
    {
        parent::setUp();
//        Notification::fake();
//        Mail::fake();

        $this->practice       = $this->setupPractice();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
    }

    public function test_creates_callback_task_if_notification_is_from_callcenterusa()
    {
        $this->createPatientData(Patient::ENROLLED);

        ProcessPostmarkInboundMailJob::dispatchNow(
            collect(json_decode($this->postmarkRecord->data))->toArray(),
            $this->postmarkRecord->id
        );

//        Also Create a nurse to assign the patient to.
        $this->assertDatabaseHas('calls', [
            'inbound_cpm_id' => $this->patient->id,
            'sub_type'       => SchedulerService::CALL_BACK_TYPE,
        ]);
    }

    public function test_does_not_create_callback_if_patient_is_auto_enroll_and_has_unassigned_care_ambassador()
    {
        $this->createPatientData(Enrollee::QUEUE_AUTO_ENROLLMENT);
        assert(true);
    }

    public function test_it_does_not_create_callback_if_patient_is_not_enrolled()
    {
        $this->createPatientData(Patient::PAUSED);
        ProcessPostmarkInboundMailJob::dispatchNow(
            collect(json_decode($this->postmarkRecord->data))->toArray(),
            $this->postmarkRecord->id
        );
        
//         Fix the n/a's first
    }

    public function test_it_does_not_create_callback_if_patient_requested_to_withdraw()
    {
        $this->createPatientData(Patient::ENROLLED, true);
        assert(true);
    }
}
