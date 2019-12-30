<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\AppConfig;
use App\Call;
use App\Jobs\CreateNurseInvoices;
use App\Jobs\StoreTimeTracking;
use App\Models\CPM\CpmProblem;
use App\Note;
use App\Traits\Tests\UserHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Config\NurseCcmPlusConfig;
use Symfony\Component\HttpFoundation\ParameterBag;
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
 * Class NursePaymentAlgo.
 */
class NursePaymentAlgo extends TestCase
{
    use UserHelpers;

    /** @var Location $location */
    protected $location;

    /** @var User $provider */
    protected $provider;

    protected function setUp()
    {
        parent::setUp();
        (new \ChargeableServiceSeeder())->run();
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
    public function test_ccm_plus_algo_over_20_total_over_30()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);
        $this->addTime($nurse, $patient, 10, true, true);
        $this->addTime($nurse, $patient, 10, false, false);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $pay = $invoices->first()->invoice_data['baseSalary'];
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
    public function test_ccm_plus_algo_over_20_total_under_30()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);
        $this->addTime($nurse, $patient, 10, true, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $pay = $invoices->first()->invoice_data['baseSalary'];
        self::assertEquals($nurseVisitFee, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - CCM Plus Algo - Variable Rate
     * - CCM = 45 minutes
     * - Total CPM time = 45 minutes
     * - CCM Plus (G2058).
     *
     * Result: $25.00. Nurse Visit Fee $12.50 in 0-20 ccm range + 20-40 ccm range. Hourly rate yields $10.
     *
     * @throws \Exception
     */
    public function test_ccm_plus_algo_over_40()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);
        $this->addTime($nurse, $patient, 10, true, false);
        $this->addTime($nurse, $patient, 10, true, true);
        $this->addTime($nurse, $patient, 10, true, false);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $pay = $invoices->first()->invoice_data['baseSalary'];
        self::assertEquals($nurseVisitFee * 2, $pay);
    }

    /**
     * - Hourly Rate = 10$
     * - CCM Plus Algo - Variable Rate
     * - CCM = 65 minutes
     * - Total CPM time = 65 minutes
     * - CCM Plus (G2058).
     *
     * Result: $37.50. Nurse Visit Fee $12.50 x 3. Hourly rate yields $20.
     *
     * @throws \Exception
     */
    public function test_ccm_plus_algo_over_60()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);
        $this->addTime($nurse, $patient, 10, true, false);
        $this->addTime($nurse, $patient, 10, true, true);
        $this->addTime($nurse, $patient, 10, true, false);
        $this->addTime($nurse, $patient, 10, true, false);
        $this->addTime($nurse, $patient, 10, true, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData  = $invoices->first()->invoice_data;
        $fixedRatePay = $invoiceData['fixedRatePay'];
        self::assertEquals(20.00, $fixedRatePay);

        $pay = $invoiceData['baseSalary'];
        self::assertEquals($nurseVisitFee * 3, $pay);
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
    public function test_ccm_plus_algo_under_20_total_over_30()
    {
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate, true);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);
        $this->addTime($nurse, $patient, 20, false, false);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $pay = $invoices->first()->invoice_data['baseSalary'];
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
    public function test_ccm_plus_algo_under_20_total_under_20()
    {
        $nurseHourlyRate = 10.0;
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate, true);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $pay = $invoices->first()->invoice_data['baseSalary'];
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
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, false);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        self::assertEquals($nurseHourlyRate, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice();
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, false);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        self::assertEquals($nurseHourlyRate, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice();
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        self::assertEquals($nurseHourlyRate, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice();
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        self::assertEquals($nurseHourlyRate, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);
        $this->addTime($nurse, $patient, 20, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        self::assertEquals($nurseHourlyRate * 2, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice();
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);
        $this->addTime($nurse, $patient, 20, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        self::assertEquals($nurseHourlyRate * 2, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);
        $this->addTime($nurse, $patient, 30, false);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $pay = $invoices->first()->invoice_data['baseSalary'];
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
        $practice        = $this->setupPractice();
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $nurse           = $this->setupNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);
        $this->addTime($nurse, $patient, 30, false);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $pay = $invoices->first()->invoice_data['baseSalary'];
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
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 15, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        self::assertEquals($nurseHourlyRate / 2, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice();
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $nurse           = $this->setupNurse($practice->id, false, $nurseHourlyRate);
        $patient         = $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, false);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        self::assertEquals($nurseHourlyRate, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice();
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, false);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        self::assertEquals($nurseHourlyRate, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice();
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $pay = $invoices->first()->invoice_data['baseSalary'];
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
        $practice        = $this->setupPractice();
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $pay = $invoices->first()->invoice_data['baseSalary'];
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
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);
        $this->addTime($nurse, $patient, 20, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        self::assertEquals(20.00, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice();
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 25, true);
        $this->addTime($nurse, $patient, 20, true);
        $this->addTime($nurse, $patient, 20, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        self::assertEquals(20.00, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);
        $this->addTime($nurse, $patient, 30, false);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $pay = $invoices->first()->invoice_data['baseSalary'];
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
        $practice        = $this->setupPractice();
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);
        $this->addTime($nurse, $patient, 30, false);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $pay = $invoices->first()->invoice_data['baseSalary'];
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
        $practice        = $this->setupPractice(true);
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $invoices->first()->invoice_data['baseSalary']);
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
        $practice        = $this->setupPractice();
        $this->provider  = $this->createUser($practice->id);
        $nurse           = $this->setupNurse($practice->id, true, $nurseHourlyRate);
        $patient         = $this->setupPatient($practice);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $this->addTime($nurse, $patient, 10, true);

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        //0.5 hours * 30 = 15.0
        self::assertEquals($nurseHourlyRate / 2, $invoices->first()->invoice_data['baseSalary']);
    }

    /**
     * - Hourly Rate $20
     * - High Rate $29
     * - Low Rate $10
     * - Visit Fee $12.50
     * - Variable Pay = true.
     *
     * Two nurses, 1 patient with ccm plus algo.
     * Nurse 1 has successful calls in 0-20 and 20-40 ranges.
     * Nurse 2 has successful call in 0-20 range only.
     * Nurse 1 has 15 minutes in 0-20 range and 10 minutes in 20-40 range.
     * Nurse 2 has 5 minutes in 0-20 range and 10 minutes in 20-40 range.
     *
     * Result:
     * Nurse 1 -> $10: 15/20 * $12.50 vs minimum 30 minutes * $20
     * Nurse 2 -> $10: 5/20: 5/20 * $12.50 + 10/20 * $12.50 vs minimum 30 minutes * $20
     */
    public function test_two_nurses_one_patient_one_nurse_has_more_successful_calls()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 20.0;
        $practice        = $this->setupPractice(true);
        $nurse1          = $this->setupNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $nurse2          = $this->setupNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $this->addTime($nurse1, $patient, 15, true, true);
        $this->addTime($nurse2, $patient, 15, true, true);
        $this->addTime($nurse1, $patient, 10, true, false);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse1->id, $nurse2->id],
            false,
            null,
            true
        ))->handle();

        $invoice1Data    = $invoices->first()->invoice_data;
        $fixedRatePay    = $invoice1Data['fixedRatePay'];
        $variableRatePay = $invoice1Data['variableRatePay'];
        $pay             = $invoice1Data['baseSalary'];

        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(9.38, $variableRatePay);
        self::assertEquals(10.00, $pay);

        $invoice2Data    = $invoices->last()->invoice_data;
        $fixedRatePay    = $invoice2Data['fixedRatePay'];
        $variableRatePay = $invoice2Data['variableRatePay'];
        $pay             = $invoice2Data['baseSalary'];

        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(9.38, $variableRatePay);
        self::assertEquals(10.00, $pay);
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
     * Nurse 1 -> $15.63: 15/20 * $12.50 + 10/20 * $12.50 vs minimum 30 minutes * $20
     * Nurse 2 -> $9.38: 5/20 * $12.50 + 10/20 * $12.50 vs minimum 30 minutes * $20
     */
    public function test_two_nurses_one_patient_successful_calls_in_all_ranges()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 20.0;
        $practice        = $this->setupPractice(true);
        $nurse1          = $this->setupNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $nurse2          = $this->setupNurse($practice->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient         = $this->setupPatient($practice);

        $this->addTime($nurse1, $patient, 15, true, true);
        $this->addTime($nurse2, $patient, 15, true, true);
        $this->addTime($nurse1, $patient, 10, true, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse1->id, $nurse2->id],
            false,
            null,
            true
        ))->handle();

        $invoice1Data = $invoices->first()->invoice_data;
        $fixedRatePay = $invoice1Data['fixedRatePay'];
        $pay          = $invoice1Data['baseSalary'];

        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(15.63, $pay);

        $invoice2Data    = $invoices->last()->invoice_data;
        $fixedRatePay    = $invoice2Data['fixedRatePay'];
        $variableRatePay = $invoice2Data['variableRatePay'];
        $pay             = $invoice2Data['baseSalary'];

        self::assertEquals(10.00, $fixedRatePay);
        self::assertEquals(9.38, $variableRatePay);
        self::assertEquals(10.00, $pay);
    }

    /**
     * Two patients, one with default algo (practice does not have ccm plus)
     * and the other with ccm plus algo.
     *
     * - Hourly Rate $20
     * - High Rate $30
     * - Low Rate $10
     * - Visit Fee $12.50
     * - Variable Pay = true
     * - Patient 1 = 25 minutes
     * - Patient 2 = 25 minutes
     *
     * Result:
     * Patient 1 -> $10.83 ($30 * 20/60 + $10 * 5/60) vs $10 hourly rate (minimum 30 minutes * 20)
     * Patient 2 -> $12.50 vs $10 hourly rate (minimum 30 minutes * 20)
     * Total -> $23.33
     */
    public function test_two_patients_default_and_ccm_plus_algo()
    {
        $nurseVisitFee   = 12.50;
        $nurseHourlyRate = 20.0;
        $practice1       = $this->setupPractice(false);
        $practice2       = $this->setupPractice(true);
        //$this->provider  = $this->createUser($practice1->id);
        $nurse    = $this->setupNurse($practice1->id, true, $nurseHourlyRate, true, $nurseVisitFee);
        $patient1 = $this->setupPatient($practice1);
        $patient2 = $this->setupPatient($practice2);

        $this->addTime($nurse, $patient1, 10, true, true);
        $this->addTime($nurse, $patient2, 10, true, true);
        $this->addTime($nurse, $patient1, 15, true, true);
        $this->addTime($nurse, $patient2, 15, true, true);

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $invoices = (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        $invoiceData  = $invoices->first()->invoice_data;
        $fixedRatePay = $invoiceData['fixedRatePay'];
        $pay          = $invoiceData['baseSalary'];

        self::assertEquals(20.00, $fixedRatePay);
        self::assertEquals(23.33, $pay);
    }

    /**
     * Add billable (ccm time) or not to a patient and credit nurse.
     */
    private function addTime(User $nurse, User $patient, int $minutes, bool $billable, bool $withSuccessfulCall = false)
    {
        if ($withSuccessfulCall) {
            /** @var Note $fakeNote */
            $fakeNote             = \factory(Note::class)->make();
            $fakeNote->author_id  = $nurse->id;
            $fakeNote->patient_id = $patient->id;
            $fakeNote->status     = Note::STATUS_COMPLETE;
            $fakeNote->save();

            /** @var Call $fakeCall */
            $fakeCall                  = \factory(Call::class)->make();
            $fakeCall->note_id         = $fakeNote->id;
            $fakeCall->status          = Call::REACHED;
            $fakeCall->inbound_cpm_id  = $patient->id;
            $fakeCall->outbound_cpm_id = $nurse->id;
            $fakeCall->save();
        }

        $seconds = $minutes * 60;
        $bag     = new ParameterBag();
        $bag->add([
            'providerId' => $nurse->id,
            'patientId'  => $billable
                ? $patient->id
                : 0,
            'activities' => [
                [
                    'is_behavioral' => false,
                    'duration'      => $seconds,
                    'start_time'    => Carbon::now(),
                    'name'          => $withSuccessfulCall
                        ? 'Patient Note Creation'
                        : 'test',
                    'title'     => 'test',
                    'url'       => 'test',
                    'url_short' => 'test',
                ],
            ],
        ]);
        (new StoreTimeTracking($bag))->handle();
    }

    private function setupNurse(
        int $practiceId,
        bool $variableRate = true,
        float $hourlyRate = 29.0,
        bool $enableCcmPlus = false,
        float $visitFee = 12.50
    ) {
        $nurse                              = $this->createUser($practiceId, 'care-center');
        $nurse->nurseInfo->is_variable_rate = $variableRate;
        $nurse->nurseInfo->hourly_rate      = $hourlyRate;
        $nurse->nurseInfo->visit_fee        = $visitFee;
        $nurse->nurseInfo->save();

        if ($enableCcmPlus) {
            $current = implode(',', NurseCcmPlusConfig::enabledForUserIds());

            AppConfig::updateOrCreate(
                [
                    'config_key' => NurseCcmPlusConfig::NURSE_CCM_PLUS_ENABLED_FOR_USER_IDS,
                ],
                [
                    'config_value' => $current.(empty($current)
                            ? ''
                            : ',').$nurse->id,
                ]
            );
        }

        return $nurse;
    }

    private function setupPatient(Practice $practice)
    {
        $patient = $this->createUser($practice->id, 'participant');
        $patient->setPreferredContactLocation($this->location->id);
        $patient->patientInfo->save();
        $cpmProblems = CpmProblem::get();
        $ccdProblems = $patient->ccdProblems()->createMany([
            ['name' => 'test'.str_random(5)],
            ['name' => 'test'.str_random(5)],
            ['name' => 'test'.str_random(5)],
        ]);
        foreach ($ccdProblems as $problem) {
            $problem->cpmProblem()->associate($cpmProblems->random());
            $problem->save();
        }

        return $patient;
    }

    private function setupPractice(bool $addCcmPlusServices = false)
    {
        $practice       = factory(Practice::class)->create();
        $this->location = Location::firstOrCreate([
            'practice_id' => $practice->id,
        ]);

        $ccmService            = ChargeableService::where('code', '=', ChargeableService::CCM)->first();
        $sync                  = [];
        $sync[$ccmService->id] = ['amount' => 29.0];
        if ($addCcmPlusServices) {
            $ccmPlus40            = ChargeableService::where('code', '=', ChargeableService::CCM_PLUS_40)->first();
            $ccmPlus60            = ChargeableService::where('code', '=', ChargeableService::CCM_PLUS_60)->first();
            $sync[$ccmPlus40->id] = ['amount' => 28.0];
            $sync[$ccmPlus60->id] = ['amount' => 28.0];
        }

        $practice->chargeableServices()->sync($sync);

        return $practice;
    }
}
