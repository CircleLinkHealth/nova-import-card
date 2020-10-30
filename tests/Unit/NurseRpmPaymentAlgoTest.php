<?php

namespace Tests\Unit;

use App\Jobs\CreateNurseInvoices;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\TimeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NurseRpmPaymentAlgoTest extends TestCase
{
    use PracticeHelpers;
    use TimeHelpers;
    use UserHelpers;
    
    private int $rpmChargeableServiceId;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->rpmChargeableServiceId = ChargeableService::firstWhere('code', '=', ChargeableService::RPM)->id;
    }
    
    /**
     * - CCM Plus algo (new algo)
     * - Hourly Rate = $17
     * - Visit Fee = $13.
     *
     * - Patient 1 -> 30 minutes
     * - Patient 2 -> 35 minutes
     * - Patient 3 -> 35 minutes
     * - Total CPM time = 100 minutes (3 patients)
     *
     * Result: $39 (3 visits)
     *
     * @throws \Exception
     */
    public function test_rpm_ccm_plus_alt_algo()
    {
        $practice = $this->setupPractice(true, false, false, true, true);
        $nurse    = $this->getNurse($practice->id, true, 17, true, 13);
        $patient1 = $this->setupPatient($practice, false, false, true);
        $patient2 = $this->setupPatient($practice, false, false, true);
        $patient3 = $this->setupPatient($practice, false, false, true);
        
        $this->addTime($nurse, $patient1, 20, true, 1, $this->rpmChargeableServiceId);
        $this->addTime($nurse, $patient1, 10, true, 1, $this->rpmChargeableServiceId);
        
        $this->addTime($nurse, $patient2, 20, true, 1, $this->rpmChargeableServiceId);
        $this->addTime($nurse, $patient2, 15, true, 1, $this->rpmChargeableServiceId);
        
        $this->addTime($nurse, $patient3, 20, true, 1, $this->rpmChargeableServiceId);
        $this->addTime($nurse, $patient3, 15, true, 1, $this->rpmChargeableServiceId);
        
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
        
        $invoice1Data = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $visitsCount     = $invoice1Data['visitsCount'];
        $fixedRatePay    = $invoice1Data['fixedRatePay'];
        $variableRatePay = $invoice1Data['variableRatePay'];
        $pay             = $invoice1Data['baseSalary'];
        
        self::assertEquals(3, $visitsCount);
        self::assertEquals(34, $fixedRatePay);
        self::assertEquals(39, $variableRatePay);
        self::assertEquals(39, $pay);
    }
    
    /**
     * - CCM Plus algo (new algo)
     * - Hourly Rate = $17
     * - Visit Fee = $13.
     *
     * - Patient 1 -> 60 minutes
     * - Patient 2 -> 35 minutes
     * - Total CPM time = 95 minutes (2 patients)
     *
     * Result: $34 (95 minutes rounded to 2 hours * $17)
     *
     * @throws \Exception
     */
    public function test_rpm_hourly_rate_algo()
    {
        $practice = $this->setupPractice(true, false, false, true);
        $nurse    = $this->getNurse($practice->id, true, 17, true, 13);
        $patient1 = $this->setupPatient($practice, false, true);
        $patient2 = $this->setupPatient($practice, false, true);
        
        $this->addTime($nurse, $patient1, 20, true, 1, $this->pcmChargeableServiceId);
        $this->addTime($nurse, $patient1, 20, true, 1, $this->pcmChargeableServiceId);
        $this->addTime($nurse, $patient1, 20, true, 1, $this->pcmChargeableServiceId);
        
        $this->addTime($nurse, $patient2, 20, true, 1, $this->pcmChargeableServiceId);
        $this->addTime($nurse, $patient2, 15, true, 1, $this->pcmChargeableServiceId);
        
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
        
        $invoice1Data = NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first()->invoice_data;
        $visitsCount     = $invoice1Data['visitsCount'];
        $fixedRatePay    = $invoice1Data['fixedRatePay'];
        $variableRatePay = $invoice1Data['variableRatePay'];
        $pay             = $invoice1Data['baseSalary'];
        
        self::assertEquals(2, $visitsCount);
        self::assertEquals(34, $fixedRatePay);
        self::assertEquals(26, $variableRatePay);
        self::assertEquals(34, $pay);
    }
    
    private function getNurse(
        $practiceId,
        bool $variableRate = true,
        float $hourlyRate = 29.0,
        bool $enableCcmPlus = false,
        float $visitFee = null
    ) {
        $nurse = $this->createUser($practiceId, 'care-center');
        
        return $this->setupNurse($nurse, $variableRate, $hourlyRate, $enableCcmPlus, $visitFee);
    }
}
