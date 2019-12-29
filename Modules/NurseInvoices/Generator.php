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

    /**
     * @return Collection
     */
    public function createAndNotifyNurses()
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

                $variablePayCalculator = new VariablePayCalculator(
                    $nurseUsers->where('nurseInfo.is_variable_rate', true)->pluck('nurseInfo.id')->all(),
                    $this->startDate,
                    $this->endDate
                );

                $variablePayMap = $variablePayCalculator->getForNurses();

                $nurseSystemTimeMap->each(
                    function ($nurseAggregatedTotalTime) use (
                        $nurseUsers,
                        $variablePayMap,
                        $invoices,
                        $variablePayCalculator
                    ) {
                        $userId = $nurseAggregatedTotalTime->first()->first()->user_id;

                        /** @var User $user */
                        $user = $nurseUsers->firstWhere('id', '=', $userId);

                        if ( ! $user) {
                            throw new \Exception("User `$userId` not found");
                        }

                        $variablePaySummary = $variablePayMap->filter(function ($f) use ($user) {
                            return $f->nurse_id === $user->nurseInfo->id;
                        });

                        $viewModel = $this->createViewModel(
                            $user,
                            $nurseAggregatedTotalTime,
                            $variablePaySummary,
                            $variablePayCalculator
                        );

                        if ($this->storeInvoicesForNurseReview) {
                            $invoice = $this->saveInvoiceData($user->nurseInfo->id, $viewModel, $this->startDate);
                            $invoices->push($invoice);
                        }
                    }
                );
            }
        );

        return $invoices;
    }

    /**
     * @return Invoice
     */
    private function createViewModel(
        User $nurse,
        Collection $aggregatedTotalTime,
        Collection $variablePaySummary,
        VariablePayCalculator $variablePayCalculator
    ) {
        return new Invoice(
            $nurse,
            $this->startDate,
            $this->endDate,
            $aggregatedTotalTime,
            $variablePaySummary,
            $variablePayCalculator
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

    /**
     * @param $nurseInfoId
     * @param $viewModel
     *
     * @return mixed
     */
    private function saveInvoiceData($nurseInfoId, $viewModel, Carbon $startDate)
    {
        return NurseInvoice::updateOrCreate(
            [
                'month_year'    => $startDate,
                'nurse_info_id' => $nurseInfoId,
            ],
            [
                'invoice_data' => $viewModel->toArray(),
            ]
        );
    }
}
