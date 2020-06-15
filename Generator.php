<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use Carbon\Carbon;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Jobs\GenerateNurseInvoice;

class Generator
{
    /**
     * @var Carbon
     */
    protected $endDate;

    /**
     * @var array
     */
    protected $nurseUserIds;

    /**
     * @var PdfService
     */
    protected $pdfService;

    /**
     * @var \Illuminate\Foundation\Application|mixed|SaveInvoicesService
     */
    protected $saveInvoices;

    /**
     * @var Carbon
     */
    protected $startDate;
    /**
     * @var bool
     */
    protected $storeInvoicesForNurseReview;
    /**
     * @var bool
     */
    private $sendToCareCoaches;

    /**
     * Generator constructor.
     *
     * @param bool $sendToCareCoaches
     * @param bool $storeInvoicesForNurseReview
     */
    public function __construct(
        array $nurseUserIds,
        Carbon $startDate,
        Carbon $endDate,
        $sendToCareCoaches = false,
        $storeInvoicesForNurseReview = false
    ) {
        $this->pdfService                  = app(PdfService::class);
        $this->startDate                   = $startDate;
        $this->endDate                     = $endDate;
        $this->sendToCareCoaches           = $sendToCareCoaches;
        $this->nurseUserIds                = $nurseUserIds;
        $this->storeInvoicesForNurseReview = $storeInvoicesForNurseReview;
    }

    public function createAndNotifyNurses()
    {
        $this->nurseUsers()->chunk(
            5,
            function ($nurseUsers) use (&$invoices) {
                $delay = 10;

                foreach ($nurseUsers as $nurseUser) {
                    GenerateNurseInvoice::dispatch(
                        $nurseUser,
                        $this->startDate,
                        $this->endDate
                    )->delay(now()->addSeconds($delay));
                    $delay = $delay + 10;
                }
            }
        );
    }

    /**
     * Fetch necessary CareCoach data to create the invoices.
     *
     * @return mixed
     */
    private function nurseUsers()
    {
        return User::withTrashed()
            ->careCoaches()
            ->has('nurseInfo')
            ->when(
                is_array($this->nurseUserIds) && ! empty($this->nurseUserIds),
                function ($q) {
                    $q->whereIn('id', $this->nurseUserIds);
                }
            )
            ->when(
                empty($this->nurseUserIds),
                function ($q) {
                    $q->whereHas(
                        'pageTimersAsProvider',
                        function ($s) {
                            $s->whereBetween(
                                'start_time',
                                [
                                    $this->startDate->copy()->startOfDay(),
                                    $this->endDate->copy()->endOfDay(),
                                ]
                            );
                        }
                    )
                        ->whereHas(
                            'nurseInfo',
                            function ($s) {
                                $s->where('is_demo', false);
                            }
                        );
                }
            );
    }
}
