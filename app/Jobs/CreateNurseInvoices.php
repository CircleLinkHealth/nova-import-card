<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Generator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateNurseInvoices implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;
    /**
     * @var Carbon
     */
    protected $endDate;
    /**
     * @var int
     */
    protected $nurseExtras;
    /**
     * @var array
     */
    protected $nurseUserIds;
    /**
     * @var int
     */
    protected $requestedBy;
    /**
     * @var bool
     */
    protected $sendToCareCoaches;
    /**
     * @var Carbon
     */
    protected $startDate;
    /**
     * @var bool
     */
    protected $storeInvoicesForNurseReview;

    /**
     * Create a new job instance.
     *
     * @param int  $requestedBy
     * @param bool $storeInvoicesForNurseReview
     */
    public function __construct(
        Carbon $startDate,
        Carbon $endDate,
        array $nurseUserIds,
        bool $sendToCareCoaches = false,
        int $requestedBy = null,
        $storeInvoicesForNurseReview = false
    ) {
        $this->nurseUserIds                = $nurseUserIds;
        $this->startDate                   = $startDate->startOfDay();
        $this->endDate                     = $endDate->endOfDay();
        $this->requestedBy                 = $requestedBy;
        $this->sendToCareCoaches           = $sendToCareCoaches;
        $this->storeInvoicesForNurseReview = $storeInvoicesForNurseReview;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $generator = new Generator($this->nurseUserIds, $this->startDate, $this->endDate, $this->sendToCareCoaches, $this->storeInvoicesForNurseReview);
        $generator->createAndNotifyNurses();
    }
}
