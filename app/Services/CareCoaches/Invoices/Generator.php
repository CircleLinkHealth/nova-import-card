<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CareCoaches\Invoices;

use App\Notifications\NurseInvoiceCreated;
use App\Services\PdfService;
use App\ViewModels\CareCoachInvoiceViewModel;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;

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
     * @var Carbon
     */
    protected $startDate;
    /**
     * @todo: deprecate
     *
     * @var bool
     */
    private $sendToCareCoaches;

    public function __construct(array $nurseUserIds, Carbon $startDate, Carbon $endDate, $sendToCareCoaches)
    {
        $this->pdfService        = app(PdfService::class);
        $this->startDate         = $startDate;
        $this->endDate           = $endDate;
        $this->sendToCareCoaches = $sendToCareCoaches;
        $this->nurseUserIds      = $nurseUserIds;
    }

    /**
     * @return Collection
     */
    public function generate()
    {
        $invoices = collect();

        $this->nurseUsers()->chunk(
            20,
            function ($nurseUsers) use (&$invoices) {
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
                    function ($nurseAggregatedTotalTime) use (
                        $nurseUsers,
                        $variablePayMap,
                        $invoices
                    ) {
                        $viewModel = $this->createViewModel($nurseAggregatedTotalTime, $nurseUsers, $variablePayMap);
                        $invoices->push($this->createPdf($viewModel));
                    }
                );
            }
        );

        return $invoices;
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
     * @param CareCoachInvoiceViewModel $viewModel
     *
     * @throws \Exception
     *
     * @return array
     */
    private function createPdf(CareCoachInvoiceViewModel $viewModel)
    {
        $name = trim($viewModel->user->getFullName()).'-'.Carbon::now()->toDateString();
        $link = $name.'.pdf';

        $pdfPath = $this->pdfService->createPdfFromView(
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
}
