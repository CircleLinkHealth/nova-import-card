<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use App\Notifications\NurseInvoiceCreated;
use App\Services\PdfService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\ViewModels\Invoice;
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

    public function __construct(
        array $nurseUserIds,
        Carbon $startDate,
        Carbon $endDate,
        $sendToCareCoaches = false
    ) {
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
                        $userId = $nurseAggregatedTotalTime->first()->first()->user_id;
                        $user = $nurseUsers->firstWhere('id', '=', $userId);

                        if ( ! $user) {
                            throw new \Exception("User `$userId` not found");
                        }

                        $viewModel = $this->createViewModel($user, $nurseAggregatedTotalTime, $variablePayMap);

                        $this->saveInvoiceData($user, $viewModel);

                        /*   $pdf = $this->createPdf($viewModel);
                           $this->forwardToCareCoach($viewModel, $pdf);

                           $invoices->push($pdf);*/
                    }
                );
            }
        );

        // return $invoices;
    }

    /**
     * @param $user
     * @param $viewModel
     */
    public function saveInvoiceData($user, $viewModel)
    {
        NurseInvoice::create(
            [
                'nurse_info_id' => $user->nurseInfo->id,
                'month_year'    => $this->startDate->toDateString(),
                'invoice_data'  => $viewModel->toArray(),
            ]
        );
    }

    /**
     * @param Invoice $viewModel
     *
     * @throws \Exception
     *
     * @return array
     */
    private function createPdf(Invoice $viewModel)
    {
        $name = trim($viewModel->nurseFullName).'-'.Carbon::now()->toDateString();
        $link = $name.'.pdf';

        $pdfPath = $this->pdfService->createPdfFromView(
            'nurseinvoices::invoice-v2',
            $viewModel->toArray(),
            storage_path("download/${name}.pdf"),
            [
                'margin-top'    => '6',
                'margin-left'   => '6',
                'margin-bottom' => '6',
                'margin-right'  => '6',
                'footer-right'  => 'Page [page] of [toPage]',
                'footer-left'   => 'report generated on '.Carbon::now()->format('m-d-Y').' at '.Carbon::now()->format(
                    'H:iA'
                    ),
                'footer-font-size' => '6',
            ]
        );

        return
            [
                'pdf_path'      => $pdfPath,
                'nurse_user_id' => $viewModel->user()->id,
                'name'          => $viewModel->user()->getFullName(),
                'email'         => $viewModel->user()->email,
                'link'          => $link,
                'date_start'    => presentDate($this->startDate),
                'date_end'      => presentDate($this->endDate),
                'email_body'    => [
                    'name'       => $viewModel->user()->getFullName(),
                    'total_time' => $viewModel->systemTimeInHours(),
                    'payout'     => $viewModel->hourlySalary(),
                ],
            ];
    }

    /**
     * @param User       $nurse
     * @param Collection $aggregatedTotalTime
     * @param Collection $variablePayMap
     *
     * @return Invoice
     */
    private function createViewModel(User $nurse, Collection $aggregatedTotalTime, Collection $variablePayMap)
    {
        return new Invoice(
            $nurse,
            $this->startDate,
            $this->endDate,
            $aggregatedTotalTime,
            $variablePayMap
        );
    }

    private function forwardToCareCoach(Invoice $viewModel, $pdf)
    {
        if ($this->sendToCareCoaches) {
            $viewModel->user()->notify(
                new NurseInvoiceCreated($pdf['link'], "{$this->startDate->englishMonth} {$this->startDate->year}")
            );
            $viewModel->user()->addMedia($pdf['pdf_path'])->toMediaCollection(
                "monthly_invoice_{$this->startDate->year}_{$this->startDate->month}"
            );
        }
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
