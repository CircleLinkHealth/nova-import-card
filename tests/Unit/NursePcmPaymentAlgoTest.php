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
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Tests\TestCase;

/**
 *
 * Class NursePaymentAlgoTest.
 */
class NursePcmPaymentAlgoTest extends TestCase
{
    use UserHelpers;
    use TimeHelpers;
    use PracticeHelpers;

    protected function setUp()
    {
        parent::setUp();
        (new \ChargeableServiceSeeder())->run();
    }


    /**
     * - CCM Plus algo (new algo)
     * - Hourly Rate = $17
     * - Visit Fee = $13
     *
     * - Patient 1 -> 60 minutes
     * - Patient 2 -> 35 minutes
     * - Total CPM time = 95 minutes (2 patients)
     *
     * Result: $34 (95 minutes rounded to 2 hours * $17)
     *
     * @throws \Exception
     */
    public function test_pcm_hourly_rate_algo() {
        //TODO
    }

    /**
     * - CCM Plus algo (new algo)
     * - Hourly Rate = $17
     * - Visit Fee = $13
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
    public function test_pcm_ccm_plus_alt_algo() {
        //TODO
    }

    /**
     * - CCM Plus algo (new algo)
     * - Hourly Rate = $17
     * - Visit Fee = $13
     *
     * - Patient 1 -> PCM: 35 minutes
     * -           -> BHI: 23 minutes
     * - Patient 2 -> PCM: 35 minutes

     * - Total CPM time = 93 minutes (2 patients - visits)
     *
     * Result: $39 (3 visits)
     *
     * @throws \Exception
     */
    public function test_pcm_and_bhi_ccm_plus_alt_algo() {
        //TODO
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
