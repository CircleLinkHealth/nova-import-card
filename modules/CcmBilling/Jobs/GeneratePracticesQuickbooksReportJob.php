<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Invoices\GeneratePracticesQuickbooksReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneratePracticesQuickbooksReportJob implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $batchId;

    private Carbon $date;

    private string $format;

    private array $practices;

    private int $requestedByUserId;

    public function __construct(array $practices, string $date, string $format, int $requestedByUserId, string $batchId)
    {
        $this->practices         = $practices;
        $this->date              = Carbon::parse($date);
        $this->format            = $format;
        $this->requestedByUserId = $requestedByUserId;
        $this->batchId           = $batchId;
    }

    public function handle()
    {
        $practiceIds = implode(',', $this->practices);
        Log::info("Invoices: Generating Complete Practice Patient Report from batch for practices: {$practiceIds}, for batch: {$this->batchId}");
        (new GeneratePracticesQuickbooksReport())
            ->setBatchId($this->batchId)
            ->setPractices($this->practices)
            ->setDate($this->date)
            ->setFormat($this->format)
            ->setRequestedUserId($this->requestedByUserId)
            ->execute();
        Log::info("Invoices: Ending Generation of Complete Practice Patient Report from batch for practices: {$practiceIds}, for batch: {$this->batchId}");
    }

    public function tags(): array
    {
        return [
            'GeneratePracticesQuickbooksReportJob',
            'practices:'.implode(', ', $this->practices),
            'date:'.$this->date,
            'batchId:'.$this->batchId,
        ];
    }
}
