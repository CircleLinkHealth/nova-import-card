<?php

namespace CircleLinkHealth\CcmBilling\Jobs\ExportPatientProblemCodes;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Notifications\PatientProblemCodesReport;
use CircleLinkHealth\CcmBilling\Repositories\BatchableStoreRepository;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

class GeneratePatientProblemCodesQuickbooksReportJob implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $requestorId;
    protected string $batchId;
    protected array $chunkIds;

    public function __construct(int $requestorId, string $batchId, array $chunkIds)
    {
        $this->requestorId = $requestorId;
        $this->batchId = $batchId;
        $this->chunkIds = $chunkIds;
    }

    public function handle()
    {

        $batchRepo           = app(BatchableStoreRepository::class);
        $batch               = collect($batchRepo->get($this->batchId, BatchableStoreRepository::JSON_TYPE, $this->chunkIds));
        $patientData = $batch
            ->map(fn($item) => $item['data'])
            ->flatten(1)
            ->toArray();


        $date  = Carbon::now()->toDateString();
        $reportName = "patient-problem-codes-$date";

        $requestor = User::findOrFail($this->requestorId);

        $media = (new FromArray("${reportName}.csv", $patientData, []))
            ->storeAndAttachMediaTo($requestor, "patient_problem_codes_report_$date");

        $requestor->notify(new PatientProblemCodesReport($media->id));
    }
}