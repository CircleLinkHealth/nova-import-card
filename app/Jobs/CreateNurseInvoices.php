<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\NurseInvoiceCreated;
use App\Notifications\NurseInvoicesCreated;
use App\Repositories\Cache\UserNotificationList;
use App\Repositories\Cache\View;
use App\Services\CareCoaches\Invoices\TotalTimeAggregator;
use App\Services\CareCoaches\Invoices\VariablePayCalculator;
use App\Services\PdfService;
use App\ViewModels\CareCoachInvoiceViewModel;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
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
     * Create a new job instance.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array  $nurseUserIds
     * @param int    $requestedBy
     * @param bool   $sendToCareCoaches
     */
    public function __construct(
        Carbon $startDate,
        Carbon $endDate,
        array $nurseUserIds,
        bool $sendToCareCoaches = false,
        int $requestedBy = null
    ) {
        $this->nurseUserIds      = $nurseUserIds;
        $this->startDate         = $startDate->startOfDay();
        $this->endDate           = $endDate->endOfDay();
        $this->requestedBy       = $requestedBy;
        $this->sendToCareCoaches = $sendToCareCoaches;
    }

    public function getAddedDuration($nurseExtras)
    {
        return $nurseExtras
            ->where('unit', 'minutes')
            ->sum('value');
    }

    public function getBonus($nurseExtras)
    {
        return $nurseExtras
            ->where('unit', 'usd')
            ->sum('value');
    }

    /**
     * Execute the job.
     *
     * @param PdfService $pdfService
     */
    public function handle(PdfService $pdfService)
    {
        $invoices = $this->generatePdfInvoices($pdfService);

        if ($invoices->isEmpty()) {
            \Log::info('Invoices not generated due to no data for selected nurses and range.');

            return;
        }

        if ($this->requestedBy) {
            $link = $this->storeInJobsCompleted($invoices);
            $this->notifyRequestor($link);
        }
    }

    private function createPdf(CareCoachInvoiceViewModel $viewModel, PdfService $pdfService)
    {
        $name = trim($viewModel->user->getFullName()).'-'.Carbon::now()->toDateString();
        $link = $name.'.pdf';

        $pdfPath = $pdfService->createPdfFromView(
            'billing.nurse.invoice-v2',
            $viewModel->toArray(),
            storage_path("download/${name}.pdf"),
            [
                'margin-top'    => '6',
                'margin-left'   => '6',
                'margin-bottom' => '6',
                'margin-right'  => '6',
                'footer-right'  => 'Page [page] of [toPage]',
                'footer-left'   => 'report generated on '.Carbon::now()->format('m-d-Y').' at '.Carbon::now(
                    )->format(
                        'H:iA'
                    ),
                'footer-font-size' => '6',
            ]
        );

        if ($this->sendToCareCoaches) {
            $viewModel->user->notify(
                new NurseInvoiceCreated($link, "{$this->startDate->englishMonth} {$this->startDate->year}")
            );
            $viewModel->user->addMedia($pdfPath)->toMediaCollection(
                "monthly_invoice_{$this->startDate->year}_{$this->startDate->month}"
            );
        }

        return
            [
                'nurse_user_id' => $viewModel->user->id,
                'name'          => $viewModel->user->getFullName(),
                'email'         => $viewModel->user->email,
                'link'          => $link,
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

        $isVariablePay = (bool) $user->nurseInfo->is_variable_rate;

        if ( ! $user) {
            throw new \Exception("User `$userId` not found");
        }

        if ($isVariablePay) {
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
            $this->getBonus($user->nurseBonuses),
            $this->getAddedDuration($user->nurseBonuses),
            $isVariablePay,
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
    private function generatePdfInvoices(PdfService $pdfService)
    {
        $invoices = collect();

        $this->nurseUsers()->chunk(
            20,
            function ($nurseUsers) use (&$invoices, $pdfService) {
                $nurseSystemTimeMap = TotalTimeAggregator::get(
                    $nurseUsers->pluck('id')->all(),
                    $this->startDate,
                    $this->endDate
                );
                $variablePayMap = VariablePayCalculator::get(
                    $nurseUsers->where('nurseInfo.is_variable_rate', true)->pluck('nurseInfo.id')->all(),
                    $this->startDate,
                    $this->endDate
                );

                $nurseSystemTimeMap->each(
                    function ($nurseAggregatedTotalTime) use ($nurseUsers, $variablePayMap, $pdfService, $invoices) {
                        $viewModel = $this->createViewModel($nurseAggregatedTotalTime, $nurseUsers, $variablePayMap);
                        $invoices->push($this->createPdf($viewModel, $pdfService));
                    }
                );
            }
        );

        return $invoices;
    }

    private function notifyRequestor($link)
    {
        User::findOrFail($this->requestedBy)
            ->notify(new NurseInvoicesCreated($link));
    }

    /**
     * @return mixed
     */
    private function nurseUsers()
    {
        return User::withTrashed()
            ->careCoaches()
            ->with(
                       [
                           'nurseBonuses' => function ($q) {
                               $q->whereBetween('date', [$this->startDate, $this->endDate]);
                           },
                           'nurseInfo',
                       ]
                   )
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
}
