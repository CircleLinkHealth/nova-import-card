<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use CircleLinkHealth\CcmBilling\Console\GenerateFakeDataForApproveBillablePatientsPage;
use CircleLinkHealth\CcmBilling\Contracts\PracticeProcessorRepository;
use CircleLinkHealth\CcmBilling\Jobs\GeneratePracticePatientsReportJob;
use CircleLinkHealth\CcmBilling\Repositories\BatchableStoreRepository;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsServiceV3;
use CircleLinkHealth\Core\Tests\TestCase;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class CcmBillingInvoicesTest extends TestCase
{
    const NUMBER_OF_PATIENTS = 2;

    private ?Practice $practice = null;
    private PracticeProcessorRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->practice   = factory(Practice::class)->create();
        $this->repository = app(PracticeProcessorRepository::class);
        Artisan::call(GenerateFakeDataForApproveBillablePatientsPage::class, [
            'practiceId'       => $this->practice->id,
            'numberOfPatients' => self::NUMBER_OF_PATIENTS,
        ]);
    }

    public function test_it_generates_patient_reports_in_batch()
    {
        $this->approvePatients();

        $date                    = now()->startOfMonth();
        $batchId                 = 'practices_invoices'.((string) Str::orderedUuid());
        $patientsToProcessPerJob = 1;
        $chunkedJobs             = $this->repository
            ->approvedBillingStatuses($this->practice->id, $date)
            ->chunkIntoJobsAndGetArray($patientsToProcessPerJob, new GeneratePracticePatientsReportJob($this->practice->id, $date, $batchId));

        self::assertEquals(self::NUMBER_OF_PATIENTS, sizeof($chunkedJobs));

        foreach ($chunkedJobs as $job) {
            $job->handle();
        }

        $batchRepo           = app(BatchableStoreRepository::class);
        $batch               = $batchRepo->get($batchId);
        $practicePatientData = collect($batch)
            ->filter(fn ($item) => $item['practice_id'] === $this->practice->id)
            ->map(fn ($item)    => $item['data'])
            ->flatten(1);

        self::assertEquals(self::NUMBER_OF_PATIENTS, $practicePatientData->count());
    }

    private function approvePatients()
    {
        /** @var ApproveBillablePatientsServiceV3 $service */
        $service = app(ApproveBillablePatientsServiceV3::class);
        $result  = $service->getBillablePatientsForMonth($this->practice->id, now()->startOfMonth());
        self::assertEquals(self::NUMBER_OF_PATIENTS, $result->summaries->total());
        $patients = $result->summaries->items();
        foreach ($patients as $patient) {
            $result2 = $service->setPatientBillingStatus($patient['report_id'], 'approved');
        }
        self::assertEquals(self::NUMBER_OF_PATIENTS, $result2['counts']['approved']);
    }
}
