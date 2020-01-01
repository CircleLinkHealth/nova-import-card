<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\NurseInvoicesCreated;
use App\Repositories\Cache\UserNotificationList;
use App\Repositories\Cache\View;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Generator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

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

    /**
     * Notify the user who requested the invoices that the job is complete.
     *
     * @param $link
     */
    private function notifyRequestor($link)
    {
        User::findOrFail($this->requestedBy)
            ->notify(new NurseInvoicesCreated($link));
    }

    /**
     * Store in our DIY view cache.
     *
     * @throws \Exception
     *
     * @return string
     */
    private function storeInJobsCompleted(Collection $invoices)
    {
        $links = $invoices->mapWithKeys(
            function ($invoice) {
                return [
                    $invoice['nurse_user_id'] => [
                        'link' => $invoice['link'],
                        'name' => $invoice['name'],
                    ],
                ];
            }
        );

        $data = $invoices->toArray();

        $viewHashKey = null;
        if ($links->isNotEmpty() && ! empty($data)) {
            $viewHashKey = (new View())->storeViewInCache(
                'billing.nurse.list',
                [
                    'invoices' => $links,
                    'data'     => $data,
                    'month'    => $this->startDate->format('F'),
                ]
            );
        }

        $userNotification = new UserNotificationList($this->requestedBy);

        if ($links->isEmpty() && empty($data)) {
            $userNotification->push('There was not data to generate Nurse Invoices.');

            return null;
        }

        $linkToView = linkToCachedView($viewHashKey);

        $userNotification->push(
            'Nurse Invoices (V2)',
            "Invoice(s) were generated for {$invoices->count()} nurse(s): {$invoices->map(function ($n) {
                return $n['name'];
            })->implode(', ')}",
            $linkToView,
            'Go to page'
        );

        return $linkToView;
    }
}
