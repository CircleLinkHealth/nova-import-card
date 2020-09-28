<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Services\ApproveBillablePatientsService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Tests\TestCase;

class ApproveBillablePatientTest extends TestCase
{
    use UserHelpers;

    /**
     * Ccm status set in revisions for month after.
     * Should be set from revisions->old_value.
     *
     * @return void
     */
    public function test_set_closed_ccm_status_from_revisions()
    {
        //set time in a previous month
        Carbon::setTestNow(Carbon::parse('2020-01-10 09:00'));
        $practice                         = factory(Practice::class)->create();
        $patient                          = $this->createUser($practice->id, 'participant');
        $patient->patientInfo->ccm_status = Patient::ENROLLED;
        $patient->patientInfo->save();

        Carbon::setTestNow(Carbon::parse('2020-02-02 09:00'));
        $monthYearDate = Carbon::now()->startOfMonth();
        $monthYearStr  = $monthYearDate->toDateString();
        PatientMonthlySummary::create(
            [
                'patient_id'             => $patient->id,
                'ccm_time'               => 1500,
                'month_year'             => $monthYearStr,
                'no_of_calls'            => 1,
                'no_of_successful_calls' => 1,
            ]
        );

        // set ccm_status in new month
        Carbon::setTestNow(Carbon::parse('2020-03-01 09:00'));
        $patient->patientInfo->ccm_status = Patient::WITHDRAWN_1ST_CALL;
        $patient->patientInfo->save();

        Carbon::setTestNow(Carbon::parse('2020-03-02 09:00'));
        /** @var ApproveBillablePatientsService $service */
        $service  = app(ApproveBillablePatientsService::class);
        $patients = $service->getBillablePatientsForMonth($practice->id, $monthYearDate);
        $jsonResp = json_decode(json_encode($patients), true);

        $this->assertNotEquals($patient->patientInfo->ccm_status, $jsonResp['summaries']['data'][0]['status']);
        $this->assertEquals(Patient::ENROLLED, $jsonResp['summaries']['data'][0]['status']);
    }

    /**
     * Ccm status not set in revisions for month.
     * Should be set from patient info.
     *
     * @return void
     */
    public function test_set_closed_ccm_status_patient_info()
    {
        //set time in a previous month
        Carbon::setTestNow(Carbon::parse('2020-01-10 09:00'));
        $practice                         = factory(Practice::class)->create();
        $patient                          = $this->createUser($practice->id, 'participant');
        $patient->patientInfo->ccm_status = Patient::ENROLLED;
        $patient->patientInfo->save();

        Carbon::setTestNow(Carbon::parse('2020-02-02 09:00'));
        $monthYearDate = Carbon::now()->startOfMonth();
        $monthYearStr  = $monthYearDate->toDateString();
        PatientMonthlySummary::create(
            [
                'patient_id'             => $patient->id,
                'ccm_time'               => 1500,
                'month_year'             => $monthYearStr,
                'no_of_calls'            => 1,
                'no_of_successful_calls' => 1,
            ]
        );

        Carbon::setTestNow(Carbon::parse('2020-03-02 09:00'));
        /** @var ApproveBillablePatientsService $service */
        $service  = app(ApproveBillablePatientsService::class);
        $patients = $service->getBillablePatientsForMonth($practice->id, $monthYearDate);
        $jsonResp = json_decode(json_encode($patients), true);

        $this->assertEquals($patient->patientInfo->ccm_status, $jsonResp['summaries']['data'][0]['status']);
    }
}
