<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Jobs\CreateNurseInvoices;
use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\TimeHelpers;
use App\Traits\Tests\UserHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Tests\TestCase;

/**
 * This class tests the new ccm plus algorithm for G2058 code.
 * - Prerequisites:
 *  1. G2058 enabled for practice
 *  2. New algo enabled for nurse
 *  3. At least 1 successful call to patient
 * - Algorithm:
 *  1. Configurable Visit Fee $12.50
 *  2. CCM > 20 mins => pay high rate
 *  3. CCM > 40 mins => pay VF
 *  4. CCM > 60 mins => pay VF
 *  5. Total payable is the sum.
 *  6. Guarantee: if total time X $20/hr > sum(VF), then pay total time X $20/hr.
 *
 * -- Edge Case:
 *  1. 2 RNs make successful call in the same 20 minute period
 *  2. then split VF based on percentage of CCM time
 *  e.g. RN1 call from 0-15 mins. RN2 call from 15-24 mins.
 *       RN1 gets 15 / (5+15) * 12.50(VF) = 9.375
 *       RN2 gets 5 / (5+15) * 12.50(VF) = 3.125
 *
 * Alternate Algorithm:
 * - Prerequisites:
 *  1. G2058 enabled for practice
 *  2. New algo enabled for nurse
 *  3. At least 1 successful call to patient
 * - Algorithm:
 *  1. CCM > 20 mins => pay high rate
 *  2. CCM > 40 mins => pay 29.25 (make configurable)
 *  4. CCM > 60 mins => pay 29.00 (make configurable)
 *  5. Total payable is the sum.
 *  6. Guarantee: if total time X $20/hr > sum, then pay total time X $20/hr.
 *
 * Class NursePaymentAlgoTest.
 */
class NursePaymentAlgoTest extends TestCase
{
    use PracticeHelpers;
    use TimeHelpers;
    use UserHelpers;

    /** @var Location */
    protected $location;

    /** @var User */
    protected $provider;

    /**
     * - Hourly Rate $20
     * - High Rate $29
     * - Low Rate $10
     * - Visit Fee $12.50
     * - Variable Pay = true.
     *
     * Two nurses, 1 patient with ccm plus algo.
     * Nurse 2 has successful call in range 20-40 minutes.
     * Nurse 1 has 15 minutes in 0-20 range.
     * Nurse 2 has 5 minutes in 0-20 range and 5 minutes in 20-40 range.
     *
     * Result:
     * Nurse 1 -> $10.00 (30 min hourly rate. no visit fee, patient has 1 billable event, no successful call)
     * Nurse 2 -> $12.50 (successful call, takes all credit)
     *
     * @throws \Exception
     */
    public function test__cp_m_1997_one_billable_event_pay_nurse_with_call()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 20.0;
        $practice        = $this->setupPractice(true, true);
        $nurse1          = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $nurse2          = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $this->addTime($nurse1, $patient, 15, true, false);
        $this->addTime($nurse2, $patient, 10, true, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse1->id, $nurse2->id],
            false,
            null,
            true
        ))->handle();

        $invoice1Data = NurseInvoice::where('nurse_info_id', $nurse1->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $fixedRatePay    = $invoice1Data['fixedRatePay'];
        $variableRatePay = $invoice1Data['variableRatePay'];
        $pay             = $invoice1Data['baseSalary'];

        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(0.00, $variableRatePay);
        self::assertEquals(10.00, $pay);

        $invoice2Data = NurseInvoice::where('nurse_info_id', $nurse2->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $fixedRatePay    = $invoice2Data['fixedRatePay'];
        $variableRatePay = $invoice2Data['variableRatePay'];
        $pay             = $invoice2Data['baseSalary'];

        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(12.50, $variableRatePay);
        self::assertEquals(12.50, $pay);
    }

    /**
     * - Hourly Rate $20
     * - High Rate $29
     * - Low Rate $10
     * - Visit Fee $12.50
     * - Variable Pay = true.
     *
     * Two nurses, 1 patient with ccm plus algo.
     * Nurse 2 has successful call in range 20-40 minutes.
     * Nurse 1 has 23 minutes in 0-20 range.
     * Nurse 2 has 5 minutes in 20-40 range.
     *
     * Result:
     * Nurse 1 -> $10.00 (30 min hourly rate. no visit fee, patient has 1 billable event, no successful call)
     * Nurse 2 -> $12.50 (successful call, takes all credit)
     *
     * @throws \Exception
     */
    public function test__cp_m_1997_one_billable_event_pay_nurse_with_call_2()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 20.0;
        $practice        = $this->setupPractice(true, true);
        $nurse1          = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $nurse2          = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $this->addTime($nurse1, $patient, 23, true, false);
        $this->addTime($nurse2, $patient, 5, true, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse1->id, $nurse2->id],
            false,
            null,
            true
        ))->handle();

        $invoice1Data = NurseInvoice::where('nurse_info_id', $nurse1->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $fixedRatePay    = $invoice1Data['fixedRatePay'];
        $variableRatePay = $invoice1Data['variableRatePay'];
        $pay             = $invoice1Data['baseSalary'];

        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(0.00, $variableRatePay);
        self::assertEquals(10.00, $pay);

        $invoice2Data = NurseInvoice::where('nurse_info_id', $nurse2->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $fixedRatePay    = $invoice2Data['fixedRatePay'];
        $variableRatePay = $invoice2Data['variableRatePay'];
        $pay             = $invoice2Data['baseSalary'];

        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(12.50, $variableRatePay);
        self::assertEquals(12.50, $pay);
    }

    /**
     * - Hourly Rate $20
     * - High Rate $29
     * - Low Rate $10
     * - Visit Fee $12.50
     * - Variable Pay = true.
     *
     * 3 nurses, 1 patient with ccm plus algo.
     * Nurse 1 0-17 minutes, no call.
     * Nurse 2 17-23 minutes, call.
     * Nurse 3 23-38 minutes, call.
     *
     * Result:
     * Nurse 1 -> $10.00 (30 min * hourly rate. no visit fee, patient has 1 billable event, no successful call)
     * Nurse 2 -> 6 / (6+15) * $12.50 = $3.57
     * Nurse 3 -> 15 / (6+15) * $12.50 = $8.93
     *
     * @throws \Exception
     */
    public function test__cp_m_1997_one_billable_event_pay_nurse_with_call_3()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 20.0;
        $practice        = $this->setupPractice(true, true);
        $nurse1          = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $nurse2          = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $nurse3          = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $this->addTime($nurse1, $patient, 17, true, false);
        $this->addTime($nurse2, $patient, 6, true, true);
        $this->addTime($nurse3, $patient, 15, true, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse1->id, $nurse2->id, $nurse3->id],
            false,
            null,
            true
        ))->handle();

        $invoice1Data = NurseInvoice::where('nurse_info_id', $nurse1->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $visitsCount     = $invoice1Data['visitsCount'];
        $fixedRatePay    = $invoice1Data['fixedRatePay'];
        $variableRatePay = $invoice1Data['variableRatePay'];
        $pay             = $invoice1Data['baseSalary'];

        self::assertEquals(0, $visitsCount);
        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(0.00, $variableRatePay);
        self::assertEquals(10.00, $pay);

        $invoice2Data = NurseInvoice::where('nurse_info_id', $nurse2->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $visitsCount     = $invoice2Data['visitsCount'];
        $fixedRatePay    = $invoice2Data['fixedRatePay'];
        $variableRatePay = $invoice2Data['variableRatePay'];
        $pay             = $invoice2Data['baseSalary'];

        self::assertEquals(0.29, $visitsCount);
        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(3.57, $variableRatePay);
        self::assertEquals(10.00, $pay);

        $invoice3Data = NurseInvoice::where('nurse_info_id', $nurse3->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $visitsCount     = $invoice3Data['visitsCount'];
        $fixedRatePay    = $invoice3Data['fixedRatePay'];
        $variableRatePay = $invoice3Data['variableRatePay'];
        $pay             = $invoice3Data['baseSalary'];

        self::assertEquals(0.71, $visitsCount);
        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(8.93, $variableRatePay);
        self::assertEquals(10.00, $pay);
    }

    /**
     * - CCM Plus Algo - Variable Rate
     * - BHI = 25 minutes
     * - CCM = 25 minutes
     * - Total CPM time = 50 minutes
     * - CCM Plus (G2058).
     *
     * Result: 30$ (50 minutes rounded to 1 hour * 30$/hr)
     *         Variable Rate Pay = (20 * 30/hr) + (5 * 10/hr) = 10.83 + 10.83 ~ 21.67
     *
     * @throws \Exception
     */
    public function test_bhi_time_and_ccm_time_ccm_plus_algo()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate, true);
        $patient         = $this->setupPatient($practice, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true, true, true);
        $this->addTime($nurse, $patient, 25, true, true, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(21.67, $variableRatePay);
        self::assertEquals($nurseHourlyRate, $fixedRatePay);
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - CCM Plus Alt Algo - Visit Fee
     * - BHI = 25 minutes
     * - CCM = 25 minutes
     * - Total CPM time = 50 minutes
     * - CCM Plus (G2058).
     *
     * Result: 30$ (50 minutes rounded to 1 hour * 30$/hr)
     *         Visit Fee = $12.50 + $12.50 = $25.00
     *
     * @throws \Exception
     */
    public function test_bhi_time_and_ccm_time_ccm_plus_alt_algo()
    {
        $visitFee        = 12.50;
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $visitFee);
        $patient         = $this->setupPatient($practice, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true, true, true);
        $this->addTime($nurse, $patient, 25, true, true, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(25.00, $variableRatePay);
        self::assertEquals($nurseHourlyRate, $fixedRatePay);
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - CCM Plus Alt Algo - Visit Fee
     * - BHI = 25 minutes
     * - CCM = 25 minutes
     * - Total CPM time = 50 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 30$ (50 minutes rounded to 1 hour * 30$/hr)
     *         Variable Rate Pay = (20 * 30/hr) + (5 * 10/hr) = 10.83 + 10.83 ~ 21.67
     *
     * @throws \Exception
     */
    public function test_bhi_time_and_ccm_time_default_algo()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, false, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true, true, true);
        $this->addTime($nurse, $patient, 25, true, true, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(21.67, $variableRatePay);
        self::assertEquals($nurseHourlyRate, $fixedRatePay);
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - CCM Plus Algo - Variable Rate
     * - BHI = 25 minutes
     * - Total CPM time = 45 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 30$ (45 minutes rounded to 1 hour * 30$/hr)
     *         Variable Rate Pay = (20 * 30/hr) + (5 * 10/hr) = 10.83
     *
     * @throws \Exception
     */
    public function test_bhi_time_ccm_plus_algo()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, false, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate, true);
        $patient         = $this->setupPatient($practice, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true, true, true);
        $this->addTime($nurse, $patient, 20, false, false, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(10.83, $variableRatePay);
        self::assertEquals($nurseHourlyRate, $fixedRatePay);
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - CCM Plus Alt Algo - Visit Fee
     * - BHI = 25 minutes
     * - Total CPM time = 45 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 30$ (45 minutes rounded to 1 hour * 30$/hr)
     *         Variable Rate Pay = $12.50
     *
     * @throws \Exception
     */
    public function test_bhi_time_ccm_plus_alt_algo()
    {
        $visitFee        = 12.50;
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, false, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $visitFee);
        $patient         = $this->setupPatient($practice, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true, true, true);
        $this->addTime($nurse, $patient, 20, false, false, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals($visitFee, $variableRatePay);
        self::assertEquals($nurseHourlyRate, $fixedRatePay);
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - BHI = 25 minutes
     * - Total CPM time = 45 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 30$ (45 minutes rounded to 1 hour * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_bhi_time_default_algo_fixed_rate()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, false, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true, true, true);
        $this->addTime($nurse, $patient, 20, false, false, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(0, $variableRatePay);
        self::assertEquals($nurseHourlyRate, $fixedRatePay);
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Default Algo - Variable Rate
     * - BHI = 25 minutes
     * - Total CPM time = 45 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 30$ (45 minutes rounded to 1 hour * 30$/hr)
     *         Variable Rate Pay = (20 * 30/hr) + (5 * 10/hr) = 10.83
     *
     * @throws \Exception
     */
    public function test_bhi_time_default_algo_variable_rate()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, false, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true, true, true);
        $this->addTime($nurse, $patient, 20, false, false, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(10.83, $variableRatePay);
        self::assertEquals($nurseHourlyRate, $fixedRatePay);
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - High Rate = $30
     * - Low Rate = $30
     * - CCM Plus Algo - Variable Rate
     * - CCM = 45 minutes
     * - Total CPM time = 45 minutes
     * - CCM Plus (G2058).
     *
     * Result: $20.17. ($30/hr in 0-20 ccm range) + ($28 + 20-40 ccm range) + ($10 * 5 minutes in 40-60 ccm range).
     * Hourly rate yields $10.
     *
     * @throws \Exception
     */
    public function test_ccm_plus_algo_over_40()
    {
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate, true);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);
        $this->addTime($nurse, $patient, 10, true, false);
        $this->addTime($nurse, $patient, 10, true, false);
        $this->addTime($nurse, $patient, 10, true, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $fixedRate = $invoiceData['fixedRatePay'];
        self::assertEquals(10, $fixedRate);

        $pay = $invoiceData['baseSalary'];
        self::assertEquals(20.17, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - CCM Plus Algo - Variable Rate
     * - CCM = 25 minutes
     * - Total CPM time = 35 minutes
     * - CCM Plus (G2058).
     *
     * Result: $12.50. Nurse Visit Fee $12.50 in 0-20 ccm range. Hourly rate yields $10.
     *
     * @throws \Exception
     */
    public function test_ccm_plus_alt_algo_over_20_total_over_30()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);
        $this->addTime($nurse, $patient, 10, true, false);
        $this->addTime($nurse, $patient, 10, false, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseVisitFee, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - CCM Plus Algo - Variable Rate
     * - CCM = 25 minutes
     * - Total CPM time = 25 minutes
     * - CCM Plus (G2058).
     *
     * Result: $12.50. Nurse Visit Fee $12.50 in 0-20 ccm range. Hourly rate yields $5.
     *
     * @throws \Exception
     */
    public function test_ccm_plus_alt_algo_over_20_total_under_30()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);
        $this->addTime($nurse, $patient, 10, true, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseVisitFee, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - CCM Plus Algo - Variable Rate
     * - CCM = 45 minutes
     * - Total CPM time = 45 minutes
     * - CCM Plus (G2058).
     *
     * Result: $25.00. Nurse Visit Fee $12.50 (0-20 ccm range) + $12.00 (20-40 ccm range). Hourly rate yields $10.
     *
     * @throws \Exception
     */
    public function test_ccm_plus_alt_algo_over_40()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);
        $this->addTime($nurse, $patient, 10, true, false);
        $this->addTime($nurse, $patient, 10, true, true);
        $this->addTime($nurse, $patient, 10, true, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals(12.50 + 12.00, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - CCM Plus Algo - Variable Rate
     * - CCM = 65 minutes
     * - Total CPM time = 65 minutes
     * - CCM Plus (G2058).
     *
     * Result: $37.50. Nurse Visit Fee $12.50 + $12.00 + $11.75. Hourly rate yields $20.
     *
     * @throws \Exception
     */
    public function test_ccm_plus_alt_algo_over_60()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);
        $this->addTime($nurse, $patient, 10, true, false);
        $this->addTime($nurse, $patient, 10, true, true);
        $this->addTime($nurse, $patient, 10, true, false);
        $this->addTime($nurse, $patient, 10, true, false);
        $this->addTime($nurse, $patient, 10, true, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        /** @var NurseInvoice $invoice */
        $invoice = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first();

        $invoiceData = $invoice->invoice_data;

        $fixedRatePay = $invoiceData['fixedRatePay'];
        self::assertEquals(20.00, $fixedRatePay);

        $pay = $invoiceData['baseSalary'];
        self::assertEquals(12.50 + 12.00 + 11.75, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - CCM Plus Algo - Variable Rate
     * - CCM = 72 minutes
     * - Total CPM time = 72 minutes
     * - CCM Plus (G2058).
     *
     * Result: $37.50. Nurse Visit Fee $12.50 + $12.00 + $11.75. Hourly rate yields $20.
     *
     * @throws \Exception
     */
    public function test_ccm_plus_alt_algo_over_60_with_long_call()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 22, true, true);
        $this->addTime($nurse, $patient, 50, true, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        /** @var NurseInvoice $invoice */
        $invoice = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first();

        $invoiceData = $invoice->invoice_data;

        $fixedRatePay = $invoiceData['fixedRatePay'];
        self::assertEquals(20.00, $fixedRatePay);

        $pay = $invoiceData['baseSalary'];
        self::assertEquals(12.50 + 12.00 + 11.75, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - CCM Plus Algo - Variable Rate
     * - CCM = 15 minutes
     * - Total CPM time = 35 minutes
     * - CCM Plus (G2058).
     *
     * Result: 10$ (35 minutes rounded to 1 hour * 10$). CCM plus algo should yield 0.
     *
     * @throws \Exception
     */
    public function test_ccm_plus_alt_algo_under_20_total_over_30()
    {
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate, true);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);
        $this->addTime($nurse, $patient, 20, false, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - CCM Plus Algo - Variable Rate
     * - CCM = 15 minutes
     * - Total CPM time = 15 minutes
     * - CCM Plus (G2058).
     *
     * Result: 5$ (minimum total time 30 minutes * 10$). CCM plus algo should yield 0.
     *
     * @throws \Exception
     */
    public function test_ccm_plus_alt_algo_under_20_total_under_20()
    {
        $nurseHourlyRate = 10.0;
        $visitFee        = 12.50;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $visitFee);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate / 2, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - CCM = 25 minutes
     * - Total CPM time = 45 minutes
     * - CCM Plus (G2058).
     *
     * Result: 30$ (45 minutes rounded to 1 hour * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_fixed_rate_over_20_total_over_30_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - CCM = 25 minutes
     * - Total CPM time = 45 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 30$ (45 minutes rounded to 1 hour * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_fixed_rate_over_20_total_over_30_no_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - CCM = 25 minutes
     * - Total CPM time = 25 minutes
     * - CCM Plus (G2058).
     *
     * Result: 15.0 (minimum cpm time 30 minutes * 30.0$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_fixed_rate_over_20_total_under_30_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - CCM = 25 minutes
     * - Total CPM time = 25 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 15.0 (minimum cpm time 30 minutes * 30.0$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_fixed_rate_over_20_total_under_30_no_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - CCM = 45 minutes
     * - Total CPM time = 45 minutes
     * - CCM Plus (G2058).
     *
     * Result: 30$ (45 minutes rounded to 1 hour * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_fixed_rate_over_40_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - CCM = 45 minutes
     * - Total CPM time = 45 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 30$ (45 minutes rounded to 1 hour * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_fixed_rate_over_40_no_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - CCM = 65 minutes
     * - Total CPM time = 65 minutes
     * - CCM Plus (G2058).
     *
     * Result: 60$ (65 minutes rounded to 2 hours * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_fixed_rate_over_60_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);
        $this->addTime($nurse, $patient, 20, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate * 2, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - CCM = 65 minutes
     * - Total CPM time = 65 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 60$ (65 minutes rounded to 2 hours * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_fixed_rate_over_60_no_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);
        $this->addTime($nurse, $patient, 20, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate * 2, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - CCM = 10 minutes
     * - Total CPM time = 40 minutes
     * - CCM Plus (G2058).
     *
     * Result: 30$ (40 minutes rounded to 1 hour * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_fixed_rate_under_20_total_over_30_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);
        $this->addTime($nurse, $patient, 30, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - CCM = 10 minutes
     * - Total CPM time = 40 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 30$ (40 minutes rounded to 1 hour * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_fixed_rate_under_20_total_over_30_no_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);
        $this->addTime($nurse, $patient, 30, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - CCM = 15 minutes
     * - Total CPM time = 15 minutes
     * - CCM Plus (G2058) Practice.
     *
     * Result: 15$ (minimum 30 minutes * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_fixed_rate_under_20_total_under_30_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate / 2, $pay);
    }

    /**
     * - Default Algo - Fixed Rate
     * - CCM = 10 minutes
     * - Total CPM time = 10 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 15.0 (minimum cpm time 30 minutes * 30.0$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_fixed_rate_under_20_total_under_30_no_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $nurse           = $this->getNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $pay);
    }

    /**
     * - Default Algo - Variable Rate
     * - CCM = 25 minutes
     * - Total CPM time = 45 minutes
     * - CCM Plus (G2058).
     *
     * Result: 30$ (45 minutes rounded to 1 hour * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_variable_rate_over_20_total_over_30_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Default Algo - Variable Rate
     * - CCM = 25 minutes
     * - Total CPM time = 45 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 30$ (45 minutes rounded to 1 hour * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_variable_rate_over_20_total_over_30_no_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Default Algo - Variable Rate
     * - CCM = 25 minutes
     * - Total CPM time = 25 minutes
     * - CCM Plus (G2058).
     *
     * Result: 15.0 (25ccm is  10$ so: minimum cpm time 30 minutes * 30.0$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_variable_rate_over_20_total_under_30_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $pay);
    }

    /**
     * - Default Algo - Variable Rate
     * - CCM = 25 minutes
     * - Total CPM time = 25 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 15.0 (25ccm is  10$ so: minimum cpm time 30 minutes * 30.0$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_variable_rate_over_20_total_under_30_no_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - Default Algo - Variable Rate
     * - CCM = 45 minutes
     * - Total CPM time = 45 minutes
     * - CCM Plus (G2058).
     *
     * Result: 14.17$ (20 minutes * $30 HR) + (25 * $10 LR) vs (45 minutes rounded to 1 hour * 10$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_variable_rate_over_40_ccm_plus_practice()
    {
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals(14.17, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - Default Algo - Variable Rate
     * - CCM = 45 minutes
     * - Total CPM time = 45 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 14.17$ (20 minutes * $30 HR) + (25 * $10 LR) vs (45 minutes rounded to 1 hour * 10$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_variable_rate_over_40_no_ccm_plus_practice()
    {
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals(14.17, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - Default Algo - Variable Rate
     * - CCM = 65 minutes
     * - Total CPM time = 65 minutes
     * - CCM Plus (G2058).
     *
     * Result: 20.00$ ((20 minutes * $30 HR) + (45 * $10 LR)) vs (65 minutes rounded to 2 hour * 10$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_variable_rate_over_60_ccm_plus_practice()
    {
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);
        $this->addTime($nurse, $patient, 20, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals(20.00, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - Default Algo - Variable Rate
     * - CCM = 65 minutes
     * - Total CPM time = 65 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 20.00$ ((20 minutes * $30 HR) + (45 * $10 LR)) vs (65 minutes rounded to 2 hour * 10$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_variable_rate_over_60_no_ccm_plus_practice()
    {
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);
        $this->addTime($nurse, $patient, 20, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals(20.00, $pay);
    }

    /**
     * - Default Algo - Variable Rate
     * - CCM = 10 minutes
     * - Total CPM time = 40 minutes
     * - CCM Plus (G2058).
     *
     * Result: 30$ (10 minutes of ccm is 6$. 40 minutes rounded to 1 hour * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_variable_rate_under_20_total_over_30_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);
        $this->addTime($nurse, $patient, 30, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Default Algo - Variable Rate
     * - CCM = 10 minutes
     * - Total CPM time = 40 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 30$ (10 minutes of ccm is 6$. 40 minutes rounded to 1 hour * 30$/hr)
     *
     * @throws \Exception
     */
    public function test_default_algo_variable_rate_under_20_total_over_30_no_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);
        $this->addTime($nurse, $patient, 30, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseHourlyRate, $pay);
    }

    /**
     * - Default Algo - Variable Rate
     * - CCM = 10 minutes
     * - Total CPM time = 10 minutes
     * - CCM Plus (G2058).
     *
     * Result: 15.0 (10 minutes of ccm is 6$. so we take total system time (minimum 30 minutes) * 30$ hourly)
     *
     * @throws \Exception
     */
    public function test_default_algo_variable_rate_under_20_total_under_30_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $pay);
    }

    /**
     * - Default Algo - Variable Rate
     * - CCM = 10 minutes
     * - Total CPM time = 10 minutes
     * - No CCM Plus (G2058).
     *
     * Result: 15.0 (10 minutes of ccm is 6$. so we take total system time (minimum 30 minutes) * 30$ hourly)
     *
     * @throws \Exception
     */
    public function test_default_algo_variable_rate_under_20_total_under_30_no_ccm_plus_practice()
    {
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->getNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $pay = $invoiceData['baseSalary'];
        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $pay);
    }

    /**
     * Total time in db should be 70 minutes.
     * Total time in invoice should be 70 minutes.
     * Nurse pay should be equivalent to 2 visits.
     *
     * @return void
     */
    public function test_nurse_time_tracked_after_start_date()
    {
        $visitFee        = 12.50;
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true, true);
        $this->createUser($practice->id);
        $nurse   = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $visitFee, now()->startOfDay());
        $patient = $this->setupPatient($practice, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, null, 20, false, false, false);
        $this->addTime($nurse, $patient, 25, true, true, false);
        $this->addTime($nurse, $patient, 25, true, true, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $totalSystemTime = $invoiceData['totalSystemTime'];
        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(70 * 60, $totalSystemTime);
        self::assertEquals(12.50 + 12.00, $variableRatePay);
        self::assertEquals($nurseHourlyRate * 2, $fixedRatePay);
        self::assertEquals($nurseHourlyRate * 2, $pay);
    }

    /**
     * Total time in db should be 90 minutes.
     * Total time in invoice should be 30 minutes.
     * Nurse pay should be equivalent to 1 hour of work (30 minutes rounded to 1 hour, 1 visit fee pays less).
     *
     * @return void
     */
    public function test_nurse_time_tracked_before_and_after_start_date()
    {
        Carbon::setTestNow(now()->startOfMonth());

        $visitFee        = 12.50;
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true, true);
        $this->createUser($practice->id);
        $nurse    = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $visitFee, now()->addDays(1)->startOfDay());
        $patient1 = $this->setupPatient($practice, true);
        $patient2 = $this->setupPatient($practice, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, null, 28, false, false, false);
        $this->addTime($nurse, $patient1, 29, true, true, false);
        $this->addTime($nurse, $patient2, 30, true, true, false, now()->addDays(2));

        //go forward to the future, so that we are in a moment of time where patient 1 and patient 2 times are in the past
        Carbon::setTestNow(now()->addDays(3));

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $totalSystemTime = $invoiceData['totalSystemTime'];
        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(30 * 60, $totalSystemTime);
        self::assertEquals($visitFee, $variableRatePay);
        self::assertEquals($nurseHourlyRate / 2, $fixedRatePay);
        self::assertEquals($nurseHourlyRate / 2, $pay);

        Carbon::setTestNow(null);
    }

    /**
     * Total time in db should be 70 minutes.
     * Total time in invoice should be 0.
     * Nurse pay should be $0.
     *
     * @return void
     */
    public function test_nurse_time_tracked_before_start_date()
    {
        Carbon::setTestNow(now()->startOfMonth());

        $visitFee        = 12.50;
        $nurseHourlyRate = 30.0;
        $practice        = $this->setupPractice(true, true, true);
        $this->createUser($practice->id);
        $nurse   = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $visitFee, now()->addDays(1)->startOfDay());
        $patient = $this->setupPatient($practice, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, null, 20, false, false, false);
        $this->addTime($nurse, $patient, 25, true, true, false);
        $this->addTime($nurse, $patient, 25, true, true, false);

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $totalSystemTime = $invoiceData['totalSystemTime'];
        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(0, $totalSystemTime);
        self::assertEquals(0, $variableRatePay);
        self::assertEquals(0, $fixedRatePay);
        self::assertEquals(0, $pay);

        Carbon::setTestNow(null);
    }

    /**
     * - Hourly Rate $20
     * - High Rate $29
     * - Low Rate $10
     * - Visit Fee $12.50
     * - Variable Pay = true.
     *
     * Two nurses, 1 patient with ccm plus algo.
     * Nurse 1 has successful call in 0-20 range only.
     * Nurse 2 has successful calls in 0-20 and 20-40 ranges.
     * Nurse 1 has 15 minutes in 0-20 range and 10 minutes in 20-40 range.
     * Nurse 2 has 5 minutes in 0-20 range and 10 minutes in 20-40 range.
     *
     * NOTE: Nurse 2 gets paid for whole of 20-40 range since he/she is the only one with a successful call in that
     * range.
     *
     * Result:
     * Nurse 1 -> $10   : $9.375 (15/20 * $12.50) VS $10 (minimum 30 minutes * $20)
     * Nurse 2 -> $15.13: $3.125 (5/20 * $12.50) + $12.00 (20/20 * $12.00) VS $10 (minimum 30 minutes * $20)
     *
     * @throws \Exception
     */
    public function test_two_nurses_one_patient_one_nurse_has_more_successful_calls()
    {
        //this test fails if run on first of month
        Carbon::setTestNow(now()->startOfMonth()->addDays(5));

        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 20.0;
        $practice        = $this->setupPractice(true, true);
        $nurse1          = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee, now()->subDay());
        $nurse2          = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $this->addTime($nurse1, $patient, 15, true, true, false, now()->subDay()->midDay());
        $this->addTime($nurse2, $patient, 15, true, true);
        $this->addTime($nurse1, $patient, 10, true, false);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse1->id, $nurse2->id],
            false,
            null,
            true
        ))->handle();

        $invoice1Data = NurseInvoice::where('nurse_info_id', $nurse1->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $fixedRatePay    = $invoice1Data['fixedRatePay'];
        $variableRatePay = $invoice1Data['variableRatePay'];
        $pay             = $invoice1Data['baseSalary'];

        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(9.38, $variableRatePay);
        self::assertEquals(10.00, $pay);

        $invoice2Data = NurseInvoice::where('nurse_info_id', $nurse2->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $fixedRatePay    = $invoice2Data['fixedRatePay'];
        $variableRatePay = $invoice2Data['variableRatePay'];
        $pay             = $invoice2Data['baseSalary'];

        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(15.13, $variableRatePay);
        self::assertEquals(15.13, $pay);

        Carbon::setTestNow(null);
    }

    /**
     * - Hourly Rate $20
     * - High Rate $29
     * - Low Rate $10
     * - Visit Fee $12.50
     * - Variable Pay = true.
     *
     * Two nurses, 1 patient with ccm plus algo.
     * Both have successful calls in 0-20 and 20-40 ranges.
     * Nurse 1 has 15 minutes in 0-20 range and 10 minutes in 20-40 range.
     * Nurse 2 has 5 minutes in 0-20 range and 10 minutes in 20-40 range.
     *
     * Result:
     * Nurse 1 -> $15.63: 15/20 * $12.50 + 10/20 * $12.00 vs minimum 30 minutes * $20
     * Nurse 2 -> $9.38: 5/20 * $12.50 + 10/20 * $12.00 vs minimum 30 minutes * $20
     *
     * @throws \Exception
     */
    public function test_two_nurses_one_patient_successful_calls_in_all_ranges()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 20.0;
        $practice        = $this->setupPractice(true, true);
        $nurse1          = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $nurse2          = $this->getNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $this->addTime($nurse1, $patient, 15, true, true);
        $this->addTime($nurse2, $patient, 15, true, true);
        $this->addTime($nurse1, $patient, 10, true, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse1->id, $nurse2->id],
            false,
            null,
            true
        ))->handle();

        $invoice1Data = NurseInvoice::where('nurse_info_id', $nurse1->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $fixedRatePay = $invoice1Data['fixedRatePay'];
        $pay          = $invoice1Data['baseSalary'];

        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(15.38, $pay);

        $invoice2Data = NurseInvoice::where('nurse_info_id', $nurse2->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $fixedRatePay    = $invoice2Data['fixedRatePay'];
        $variableRatePay = $invoice2Data['variableRatePay'];
        $pay             = $invoice2Data['baseSalary'];

        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(9.13, $variableRatePay);
        self::assertEquals(10.00, $pay);
    }

    /**
     * Two patients, nurse on ccm plus algo, one practice does not have ccm plus enabled (therefore not paid).
     *
     * - Hourly Rate $20
     * - High Rate $30
     * - Low Rate $10
     * - Visit Fee $12.50
     * - Variable Pay = true
     * - Patient 1 = 45 minutes
     * - Patient 2 = 45 minutes
     *
     * Result:
     * Patient 1 -> $12.50 vs $20 hourly rate (round up 60 minutes * 20)
     * Patient 2 -> ($12.50 + $12.00) vs $20 hourly rate (round up 60 minutes * 20)
     * Total -> $40
     *
     * @throws \Exception
     */
    public function test_two_patients_default_and_ccm_plus_alt_algo()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 20.0;
        $practice1       = $this->setupPractice(true, false);
        $practice2       = $this->setupPractice(true, true);
        //$this->provider  = $this->createUser($practice1->id);
        $nurse = $this->getNurse($practice1->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $nurse->attachRoleForPractice(Role::byName('care-center'), $practice2->id);
        $nurse->load('practices');
        $patient1 = $this->setupPatient($practice1);
        $patient2 = $this->setupPatient($practice2);

        $this->addTime($nurse, $patient1, 10, true, false);
        $this->addTime($nurse, $patient2, 10, true, false);
        $this->addTime($nurse, $patient1, 15, true, false);
        $this->addTime($nurse, $patient2, 15, true, false);
        $this->addTime($nurse, $patient1, 20, true, true);
        $this->addTime($nurse, $patient2, 20, true, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;

        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(37.00, $variableRatePay);
        self::assertEquals(40.00, $fixedRatePay);
        self::assertEquals(40.00, $pay);
    }

    public function test_visits_count_as_a_floating_number()
    {
        $practice = $this->setupPractice(true);
        $nurse1   = $this->getNurse($practice->id, true, 1, true, 12.50);
        $nurse2   = $this->getNurse($practice->id, true, 1, true, 12.50);
        $patient1 = $this->setupPatient($practice, false, false);
        $patient2 = $this->setupPatient($practice, false, false);

        $this->addTime($nurse1, $patient1, 20, true, 1);
        $this->addTime($nurse1, $patient2, 10, true, 1);
        $this->addTime($nurse2, $patient2, 10, true, 1);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse1->id, $nurse2->id],
            false,
            null,
            true
        ))->handle();

        $invoice1Data = NurseInvoice::where('nurse_info_id', $nurse1->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $visitsCount     = $invoice1Data['visitsCount'];
        $fixedRatePay    = $invoice1Data['fixedRatePay'];
        $variableRatePay = $invoice1Data['variableRatePay'];
        $pay             = $invoice1Data['baseSalary'];

        self::assertEquals(1.5, $visitsCount);
        self::assertEquals(0.5, $fixedRatePay);
        self::assertEquals(18.75, $variableRatePay);
        self::assertEquals(18.75, $pay);

        $invoice2Data = NurseInvoice::where('nurse_info_id', $nurse2->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $visitsCount     = $invoice2Data['visitsCount'];
        $fixedRatePay    = $invoice2Data['fixedRatePay'];
        $variableRatePay = $invoice2Data['variableRatePay'];
        $pay             = $invoice2Data['baseSalary'];

        self::assertEquals(0.5, $visitsCount);
        self::assertEquals(0.5, $fixedRatePay);
        self::assertEquals(6.25, $variableRatePay);
        self::assertEquals(6.25, $pay);
    }

    private function getNurse(
        $practiceId,
        bool $variableRate = true,
        float $hourlyRate = 29.0,
        bool $enableCcmPlus = false,
        float $visitFee = null,
        Carbon $startDate = null
    ) {
        $nurse = $this->createUser($practiceId, 'care-center');

        return $this->setupNurse($nurse, $variableRate, $hourlyRate, $enableCcmPlus, $visitFee, $startDate);
    }
}
