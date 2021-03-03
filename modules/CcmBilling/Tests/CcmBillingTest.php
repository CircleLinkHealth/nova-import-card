<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use CircleLinkHealth\CcmBilling\Console\GenerateFakeDataForApproveBillablePatientsPage;
use CircleLinkHealth\CcmBilling\Processors\Customer\Practice as PracticeProcessor;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsServiceV3;
use CircleLinkHealth\Core\Tests\TestCase;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Artisan;

class CcmBillingTest extends TestCase
{
    const NUMBER_OF_PATIENTS = 1;

    private ?User $admin        = null;
    private ?Practice $practice = null;

    protected function setUp(): void
    {
        parent::setUp();
        // @var Practice $practice
        $this->practice = factory(Practice::class)->create();
        Artisan::call(GenerateFakeDataForApproveBillablePatientsPage::class, [
            'practiceId'       => $this->practice->id,
            'numberOfPatients' => self::NUMBER_OF_PATIENTS,
        ]);
    }

    public function test_it_approves_abp_patients()
    {
        /** @var ApproveBillablePatientsServiceV3 $service */
        $service = app(ApproveBillablePatientsServiceV3::class);
        $result  = $service->getBillablePatientsForMonth($this->practice->id, now()->startOfMonth());

        self::assertEquals(self::NUMBER_OF_PATIENTS, $result->summaries->total());

        $billingStatus = $result->summaries->items()[0];
        $result2       = $service->setPatientBillingStatus($billingStatus['report_id'], 'approved');
        self::assertEquals(1, $result2['counts']['approved']);
    }

    public function test_it_closes_month()
    {
        /** @var ApproveBillablePatientsServiceV3 $service */
        $service = app(ApproveBillablePatientsServiceV3::class);
        $result  = $service->getBillablePatientsForMonth($this->practice->id, now()->startOfMonth());

        self::assertEquals(self::NUMBER_OF_PATIENTS, $result->summaries->total());

        $admin = $this->administrator();
        auth()->login($admin);
        $result = $service->closeMonth($admin->id, $this->practice->id, now()->startOfMonth());
        self::assertTrue($result);

        $result = $service->getBillablePatientsForMonth($this->practice->id, now()->startOfMonth());
        self::assertTrue($result->isClosed);
    }

    public function test_it_fetches_approvable_patients_for_practice()
    {
        $collection = app(PracticeProcessor::class)->fetchApprovablePatients([$this->practice->id], now()->startOfMonth());
        self::assertEquals(self::NUMBER_OF_PATIENTS, $collection->count());
    }

    public function test_it_rejects_abp_patients()
    {
        /** @var ApproveBillablePatientsServiceV3 $service */
        $service = app(ApproveBillablePatientsServiceV3::class);
        $result  = $service->getBillablePatientsForMonth($this->practice->id, now()->startOfMonth());

        self::assertEquals(self::NUMBER_OF_PATIENTS, $result->summaries->total());

        $billingStatus = $result->summaries->items()[0];
        $result2       = $service->setPatientBillingStatus($billingStatus['report_id'], 'rejected');
        self::assertEquals(1, $result2['counts']['rejected']);
    }

    private function administrator(): User
    {
        if ( ! $this->admin) {
            $this->admin = User::firstWhere('first_name', '=', 'administrator');
        }

        return $this->admin;
    }
}
