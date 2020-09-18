<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\TotalTimeAggregator;
use CircleLinkHealth\NurseInvoices\VariablePayCalculator;
use CircleLinkHealth\NurseInvoices\ViewModels\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GenerateNurseInvoice implements ShouldQueue
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
     * @var User
     */
    protected $nurseUser;
    /**
     * @var Carbon
     */
    protected $startDate;

    /**
     * Create a new job instance.
     */
    public function __construct(User $nurseUser, Carbon $startDate, Carbon $endDate)
    {
        $this->nurseUser = $nurseUser;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->nurseUser->loadMissing(
            [
                'nurseBonuses' => function ($q) {
                    $q->whereBetween('date', [$this->startDate, $this->endDate]);
                },
                'nurseInfo',
            ]
        );

        $nurseSystemTimeMap = TotalTimeAggregator::get(
            [$this->nurseUser->id],
            $this->startDate,
            $this->endDate
        );

        $variablePayCalculator = new VariablePayCalculator(
            optional($this->nurseUser->nurseInfo)->is_variable_rate
                ? [$this->nurseUser->nurseInfo->id]
                : [],
            $this->startDate,
            $this->endDate
        );

        $nurseSystemTimeMap->each(
            function ($nurseAggregatedTotalTime) use (
                $variablePayCalculator
            ) {
                $viewModel = $this->createViewModel(
                    $this->nurseUser,
                    $nurseAggregatedTotalTime,
                    $variablePayCalculator
                );

                $this->saveInvoiceData($this->nurseUser->nurseInfo->id, $viewModel, $this->startDate);
            }
        );
    }

    /**
     * @return Invoice
     */
    private function createViewModel(
        User $nurse,
        Collection $aggregatedTotalTime,
        VariablePayCalculator $variablePayCalculator
    ) {
        return new Invoice(
            $nurse,
            $this->startDate,
            $this->endDate,
            $aggregatedTotalTime,
            $variablePayCalculator
        );
    }

    /**
     * @param $nurseInfoId
     * @param $viewModel
     *
     * @return mixed
     */
    private function saveInvoiceData($nurseInfoId, Invoice $viewModel, Carbon $startDate)
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
