<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\Core\Contracts\DirectMail;
use App\Contracts\Efax;
use CircleLinkHealth\Core\Notifications\Channels\DirectMailChannel;
use App\Notifications\SendAuditReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\RateLimitedMiddleware\RateLimited;

class MakeAndDispatchAuditReports implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var Carbon
     */
    protected $date;

    /**
     * @var DirectMail
     */
    protected $directMail;

    /**
     * @var Efax
     */
    protected $eFax;

    /**
     * The Patient we are preparing a report for.
     *
     * @var User
     */
    protected $patient;
    /**
     * @var bool
     */
    private $batch;

    /**
     * Create a new job instance.
     *
     * @param Carbon $date
     */
    public function __construct(User $patient, Carbon $date = null, bool $batch = true)
    {
        $this->patient = $patient;
        $this->date    = $date ?? Carbon::now();
        $this->batch   = $batch;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $settings = $this->patient->primaryPractice->settings()->firstOrNew([]);

        $this->patient->locations->each(function (Location $location) use ($settings) {
            if ($settings->dm_audit_reports) {
                $channels[] = DirectMailChannel::class;
            }

            if ($settings->efax_audit_reports && $location->fax) {
                $channels[] = 'phaxio';
            }

            if (isset($channels)) {
                $location->notify(new SendAuditReport($this->patient, $this->date, $channels, $this->batch));
            }
        });
    }

    public function middleware()
    {
        $rateLimitedMiddleware = (new RateLimited())
            ->allow(50)
            ->everySeconds(60)
            ->releaseAfterSeconds(10);

        return [$rateLimitedMiddleware];
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHour();
    }
}
