<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\NurseInvoicesCreated;
use App\Repositories\Cache\UserNotificationList;
use App\Repositories\Cache\View;
use App\Services\PdfService;
use App\TimeTrackedPerDayView;
use App\ViewModels\CareCoachInvoiceViewModel;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CreateNurseInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Carbon
     */
    protected $endDate;
    /**
     * @var int
     */
    protected $extraTime;
    /**
     * @var string
     */
    protected $note;
    /**
     * @var array
     */
    protected $nurseUserIds;
    /**
     * @var int
     */
    protected $requestedBy;
    /**
     * @var Carbon
     */
    protected $startDate;
    /**
     * @var bool
     */
    protected $variablePay;

    /**
     * Create a new job instance.
     *
     * @param array  $nurseUserIds
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int    $requestedBy
     * @param bool   $variablePay
     * @param int    $extraTime
     * @param string $note
     */
    public function __construct(
        array $nurseUserIds,
        Carbon $startDate,
        Carbon $endDate,
        int $requestedBy,
        bool $variablePay = false,
        int $extraTime = 0,
        string $note = ''
    ) {
        $this->nurseUserIds = $nurseUserIds;
        $this->startDate    = $startDate->startOfDay();
        $this->endDate      = $endDate->endOfDay();
        $this->requestedBy  = $requestedBy;
        $this->variablePay  = $variablePay;
        $this->extraTime    = $extraTime;
        $this->note         = $note;
    }

    /**
     * Execute the job.
     *
     * @param PdfService $pdfService
     */
    public function handle(PdfService $pdfService)
    {
        $start = microtime(true);

        $nurseUsers = $this->nurseUsers();

        //time to run: 6.6966331005096
//        $start1 = microtime(true);
//        $systemTimeMap = $this->totalTimeMap();
//        $end1 = microtime(true) - $start1;

        //time to run: 0.07391095161438
//        $start2              = microtime(true);
        $nurseSystemTimeMap = $this->totalTimeMapNoView();
//        $end2                = microtime(true) - $start2;

        $invoices = $this->generatePdfInvoices($nurseUsers, $nurseSystemTimeMap, $pdfService);

        $link = $this->storeInJobsCompleted($invoices);

        $this->notifyRequestor($link);
    }

    private function createPdf(CareCoachInvoiceViewModel $viewModel, PdfService $pdfService)
    {
        $name = trim($viewModel->user->getFullName()).'-'.Carbon::now()->toDateString();

        $pdfPath = $pdfService->createPdfFromView(
            'billing.nurse.invoice-v2',
            $viewModel->toArray(),
            [
                'margin-top'       => '12',
                'margin-left'      => '12',
                'margin-bottom'    => '12',
                'margin-right'     => '12',
                'footer-right'     => 'Page [page] of [toPage]',
                'footer-font-size' => '8',
            ],
            storage_path("download/${name}.pdf")
        );

        return [
            'nurse_user_id' => $viewModel->user->id,
            'name'          => $viewModel->user->getFullName(),
            'email'         => $viewModel->user->email,
            'link'          => $name.'.pdf',
            'date_start'    => presentDate($this->startDate),
            'date_end'      => presentDate($this->endDate),
            'email_body'    => [
                'name'       => $viewModel->user->getFullName(),
                'total_time' => $viewModel->systemTimeInHours(),
                'payout'     => $viewModel->invoiceAmount(),
            ],
        ];
    }

    /**
     * @param Collection $itemizedData
     * @param Collection $nurseUsers
     * @param Collection $variablePayMap
     *
     * @throws \Exception
     *
     * @return CareCoachInvoiceViewModel
     */
    private function createViewModel(Collection $itemizedData, Collection $nurseUsers, Collection $variablePayMap)
    {
        $userId = $itemizedData->first()->first()->user_id;
        $user   = $nurseUsers->firstWhere('id', '=', $userId);

        if ( ! $user) {
            throw new \Exception("User `$userId` not found");
        }

        if ($this->variablePay) {
            $variablePaySummary = $variablePayMap->first(
                function ($value, $key) use ($user) {
                    return $key === $user->nurseInfo->id;
                }
            );
        }

        return new CareCoachInvoiceViewModel(
            $user,
            $this->startDate,
            $this->endDate,
            $itemizedData,
            $this->extraTime,
            $this->note,
            $this->variablePay,
            $variablePaySummary ?? collect()
        );
    }

    /**
     * @param Collection $nurseUsers
     * @param Collection $nurseSystemTimeMap
     * @param PdfService $pdfService
     *
     * @return Collection
     */
    private function generatePdfInvoices(Collection $nurseUsers, Collection $nurseSystemTimeMap, PdfService $pdfService)
    {
        if ($this->variablePay) {
            $variablePayMap = $this->variablePayMap($nurseUsers->pluck('nurseInfo.id')->all());
        } else {
            $variablePayMap = collect();
        }

        return $nurseSystemTimeMap->transform(
            function ($itemizedData) use ($nurseUsers, $variablePayMap) {
                return $this->createViewModel($itemizedData, $nurseUsers, $variablePayMap);
            }
        )->map(
            function ($viewModel) use ($pdfService) {
                return $this->createPdf($viewModel, $pdfService);
            }
        );
    }

    /**
     * @param string $table
     * @param string $dateTimeField
     * @param Carbon $start
     * @param Carbon $end
     * @param mixed  $isBillable
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function itemizedActivitiesQuery(
        string $table,
        string $dateTimeField,
        Carbon $start,
        Carbon $end,
        $isBillable = false
    ) {
        return \DB::table($table)
            ->select(
                \DB::raw('SUM(duration) as total_time'),
                \DB::raw("DATE_FORMAT($dateTimeField, '%Y-%m-%d') as date"),
                'provider_id as user_id',
                $isBillable
                          ? \DB::raw('TRUE as is_billable')
                          : \DB::raw('FALSE as is_billable')
                  )
            ->whereIn('provider_id', $this->nurseUserIds)
            ->whereBetween(
                $dateTimeField,
                [
                    $start,
                    $end,
                ]
                  )->groupBy('date', 'user_id');
    }

    private function notifyRequestor($link)
    {
        User::findOrFail($this->requestedBy)
            ->notify(new NurseInvoicesCreated($link));
    }

    /**
     * @return Collection|null
     */
    private function nurseUsers()
    {
        return User::withTrashed()
            ->ofType('care-center')
            ->with('nurseInfo')
            ->has('nurseInfo')
            ->whereIn('id', $this->nurseUserIds)
            ->get();
    }

    private function offlineSystemTime()
    {
        return $this->itemizedActivitiesQuery(
            (new Activity())->getTable(),
            'performed_at',
            $this->startDate,
            $this->endDate
        )->where('logged_from', 'manual_input');
    }

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
        if ( ! empty($links) && ! empty($data)) {
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

        if (empty($links) && empty($data)) {
            $userNotification->push('There was not data to generate Nurse Invoices.');

            return;
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

    private function systemTimeFromPageTimer()
    {
        return $this->itemizedActivitiesQuery(
            (new PageTimer())->getTable(),
            'start_time',
            $this->startDate,
            $this->endDate
        );
    }

    private function totalBillableTimeMap()
    {
        return $this->itemizedActivitiesQuery(
            (new Activity())->getTable(),
            'performed_at',
            $this->startDate,
            $this->endDate,
            true
        );
    }

    private function totalTimeMap()
    {
        return TimeTrackedPerDayView::whereIn('user_id', $this->nurseUserIds)
            ->whereBetween(
                'date',
                [
                    $this->startDate->toDateString(),
                    $this->endDate->toDateString(),
                ]
                                    )
            ->groupBy('date', 'user_id', 'is_billable')
            ->get()
            ->groupBy(['user_id', 'date', 'is_billable'])
            ->values();
    }

    /**
     * @return Collection
     */
    private function totalTimeMapNoView()
    {
        return \DB::query()
            ->fromSub(
                $this->systemTimeFromPageTimer()
                    ->unionAll($this->offlineSystemTime())
                    ->unionAll($this->totalBillableTimeMap()),
                'activities'
                  )
            ->select(
                \DB::raw('SUM(total_time) as total_time'),
                'date',
                'user_id',
                'is_billable'
                  )
            ->groupBy('user_id', 'date', 'is_billable')
            ->get()
            ->groupBy(['user_id', 'date'])
            ->values();
    }

    /**
     * @param array $nurseInfoIds
     *
     * @return Collection
     */
    private function variablePayMap(array $nurseInfoIds)
    {
        return NurseCareRateLog::whereIn('nurse_id', $nurseInfoIds)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->select(
                \DB::raw('SUM(increment) as total_time'),
                'ccm_type',
                \DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as date"),
                'nurse_id'
                               )
            ->groupBy('nurse_id', 'date', 'ccm_type')
            ->get()
            ->groupBy(['nurse_id', 'date', 'ccm_type']);
    }
}
