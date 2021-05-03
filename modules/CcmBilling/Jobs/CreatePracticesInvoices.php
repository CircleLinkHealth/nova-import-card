<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Services\PracticesInvoicesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatePracticesInvoices implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Carbon $date;

    public string $format;

    public array $practices;

    public int $requestedByUserId;

    public int $timeout = 900;

    /**
     * Create a new job instance.
     */
    public function __construct(string $practices, string $date, string $format, int $requestedByUserId)
    {
        $this->practices         = [$practices];
        $this->date              = Carbon::parse($date);
        $this->format            = $format;
        $this->requestedByUserId = $requestedByUserId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(PracticesInvoicesService::class)->generate($this->practices, $this->date, $this->format, $this->requestedByUserId);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            'CreatePracticesInvoices',
            'date:'.$this->date->toDateString(),
            'format:'.$this->format,
            'practices:'.implode(',', $this->practices),
            'requestedByUserId:'.$this->requestedByUserId,
        ];
    }
}
