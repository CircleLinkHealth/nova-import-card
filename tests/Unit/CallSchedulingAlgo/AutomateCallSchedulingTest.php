<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit\CallSchedulingAlgo;

use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\AppConfig\StandByNurseUser;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Support\Carbon;
use Tests\Helpers\CarePlanHelpers;
use Tests\TestCase;

class AutomateCallSchedulingTest extends TestCase
{
    use \App\Traits\Tests\UserHelpers;
    use CarePlanHelpers;

    /** @var SchedulerService */
    protected $schedulerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schedulerService = $this->app->make(SchedulerService::class);
    }

    /**
     * Test that the system will NOT schedule a call for a patient with careplan status = QA_APPROVED
     * who already has a scheduled call.
     *
     * @return void
     */
    public function test_system_does_not_schedule_call_for_careplan_qaapproved_with_existing_call()
    {
        $practice = $this->createPractice(false);
        $patient  = $this->createPatient($practice->id, true, false, false, false);
        $this->schedulerService->storeScheduledCall($patient->id, '09:00', '12:00', now()->addDay(), 'test');
        $this->schedulerService->ensurePatientHasScheduledCall($patient);
        $callsCount = $patient->inboundScheduledCalls->count();
        $callId     = $patient->inboundScheduledCalls->first()->id;
        $this->assertTrue(1 == $callsCount);
        $patient->carePlan->status = CarePlan::QA_APPROVED;
        $patient->carePlan->save();
        //reload relation
        $patient->load('inboundScheduledCalls');
        $callsCount = $patient->inboundScheduledCalls->count();
        $this->assertTrue(1 == $callsCount);
        $this->assertTrue($patient->inboundScheduledCalls->first()->id === $callId);
    }

    /**
     * Test that the system will NOT schedule a call for a patient with ccm_status enrolled
     * and already a scheduled call.
     *
     * @return void
     */
    public function test_system_does_not_schedule_call_for_enrolled_patient_with_existing_call()
    {
        $practice = $this->createPractice(false);
        $patient  = $this->createPatient($practice->id, false, false, false, false);
        $this->schedulerService->storeScheduledCall($patient->id, '09:00', '12:00', now()->addDay(), 'test');
        $this->schedulerService->ensurePatientHasScheduledCall($patient);
        $callsCount = $patient->inboundScheduledCalls->count();
        $callId     = $patient->inboundScheduledCalls->first()->id;
        $this->assertTrue(1 == $callsCount);
        $this->schedulerService->ensurePatientHasScheduledCall($patient);
        //reload relation
        $patient->load('inboundScheduledCalls');
        $callsCount = $patient->inboundScheduledCalls->count();
        $this->assertTrue(1 == $callsCount);
        $this->assertTrue($patient->inboundScheduledCalls->first()->id === $callId);
    }

    /**
     * Test that the system will schedule a call for a patient that changes ccm_status to enrolled
     * and does not already have a scheduled call.
     *
     * @return void
     */
    public function test_system_does_not_schedule_call_for_patient_status_change_to_enrolled_with_existing_call()
    {
        $practice = $this->createPractice(false);
        $patient  = $this->createPatient($practice->id, false, false, false, false);
        $this->schedulerService->storeScheduledCall($patient->id, '09:00', '12:00', now()->addDay(), 'test');
        $this->schedulerService->ensurePatientHasScheduledCall($patient);
        $callsCount = $patient->inboundScheduledCalls->count();
        $callId     = $patient->inboundScheduledCalls->first()->id;
        $this->assertTrue(1 == $callsCount);
        //switch to paused
        $patient->patientInfo->ccm_status = Patient::PAUSED;
        $patient->patientInfo->save();
        //switch to enrolled
        $patient->patientInfo->ccm_status = Patient::ENROLLED;
        $patient->patientInfo->save();
        //reload relation
        $patient->load('inboundScheduledCalls');
        $callsCount = $patient->inboundScheduledCalls->count();
        $this->assertTrue(1 == $callsCount);
        $this->assertTrue($patient->inboundScheduledCalls->first()->id === $callId);
    }

    /**
     * Test that the system will schedule a call for a patient with careplan status = QA_APPROVED
     * who does not have already a scheduled call.
     *
     * @return void
     */
    public function test_system_schedules_call_for_careplan_qaapproved()
    {
        $practice   = $this->createPractice(false);
        $patient    = $this->createPatient($practice->id, true, false, false, false);
        $callsCount = $patient->inboundScheduledCalls->count();
        $this->assertTrue(0 == $callsCount);
        $patient->carePlan->status = CarePlan::QA_APPROVED;
        $patient->carePlan->save();
        //reload relation
        $patient->load('inboundScheduledCalls');
        $callsCount = $patient->inboundScheduledCalls->count();
        $this->assertTrue(1 == $callsCount);
    }

    /**
     * Test that the system will schedule a call for a patient with ccm_status enrolled
     * with no currently scheduled call.
     *
     * @return void
     */
    public function test_system_schedules_call_for_enrolled_patient_without_call()
    {
        $practice = $this->createPractice(false);
        $patient  = $this->createPatient($practice->id, true, false, false, false);
        $updated  = CarePlan::where('user_id', $patient->id)->update([
            'status' => CarePlan::QA_APPROVED,
        ]);
        $this->schedulerService->ensurePatientHasScheduledCall($patient);
        $callsCount = $patient->inboundScheduledCalls->count();
        $this->assertTrue(1 == $callsCount);
    }

    /**
     * Test that the system will schedule a call for a patient that changes ccm_status to enrolled
     * and does not already have a scheduled call.
     *
     * @return void
     */
    public function test_system_schedules_call_for_patient_status_change_to_enrolled()
    {
        $practice = $this->createPractice(false);

        $nurse = $this->createUser($practice->id, 'care-center');
        AppConfig::set(StandByNurseUser::STAND_BY_NURSE_USER_ID_NOVA_KEY, $nurse->id);

        $patient = $this->createPatient($practice->id, false, false, false, false);
        $updated = CarePlan::where('user_id', $patient->id)->update([
            'status' => CarePlan::QA_APPROVED,
        ]);
        $callsCount = $patient->inboundScheduledCalls->count();
        $this->assertTrue(0 == $callsCount);
        $patient->patientInfo->ccm_status = Patient::ENROLLED;
        $patient->patientInfo->save();
        //reload relation
        $patient->load('inboundScheduledCalls');
        $callsCount = $patient->inboundScheduledCalls->count();
        $this->assertTrue(1 == $callsCount);
    }

    private function createPatient(
        $practiceId,
        $enrolled = true,
        $hasBhiProblem = true,
        $hasBhiConsentNote = true,
        $consentedBeforeBhiDate = true
    ) {
        $ccmStatus = $enrolled
            ? Patient::ENROLLED
            : Patient::PAUSED;
        $patient = $this->createUser($practiceId, 'participant', $ccmStatus);

        $patient->patientInfo()
            ->update([
                'ccm_status'   => $ccmStatus,
                'consent_date' => $consentedBeforeBhiDate
                    ? Carbon::parse(Patient::DATE_CONSENT_INCLUDES_BHI)->subWeek()
                    : Carbon::parse(Patient::DATE_CONSENT_INCLUDES_BHI),
            ]);

        $patient->load('patientInfo');

        if ($hasBhiConsentNote) {
            $now = Carbon::now();

            $patient->notes()
                ->create([
                    'author_id'    => factory(User::class)->create()->id,
                    'body'         => "Patient consented to receive BHI care on {$now->toDateTimeString()}",
                    'type'         => Patient::BHI_CONSENT_NOTE_TYPE,
                    'performed_at' => $now->toDateTimeString(),
                ]);
        }

        if ($hasBhiProblem) {
            $bhiProblem = CpmProblem::where('is_behavioral', '=', true)
                ->firstOrFail();

            $patient->ccdProblems()
                ->create([
                    'cpm_problem_id' => $bhiProblem->id,
                    'name'           => $bhiProblem->name,
                ]);
        }

        return $patient;
    }

    private function createPractice($bhi = false)
    {
        $practice = factory(Practice::class)->create([]);

        if ($bhi) {
            $practice->chargeableServices()
                ->attach(ChargeableService::whereCode('CPT 99484')->firstOrFail()->id);
        }

        return $practice;
    }
}
