<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use MichaelLedin\LaravelJob\Job;

class CompareCurrentAndLegacyBillingDataForPractice extends Job
{
    protected array $idsToInvestigate = [];
    protected Carbon $month;
    protected int $practiceId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $practiceId, Carbon $month = null)
    {
        $this->practiceId = $practiceId;
        $this->month      = $month ?? Carbon::now()->startOfMonth()->startOfDay();
    }

    public static function fromParameters(...$parameters)
    {
        $date = isset($parameters[1]) ? Carbon::parse($parameters[1]) : null;

        return new self((int) $parameters[0], $date);
    }

    public function getMonth(): Carbon
    {
        return $this->month;
    }

    public function getPracticeId(): int
    {
        return $this->practiceId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        User::ofType('participant')
            ->whereHas('patientInfo', fn ($pi) => $pi->enrolled())
            ->with([
                'patientSummaries' => function ($pms) {
                    $pms->with(['allChargeableServices'])
                        ->createdOn($this->getMonth(), 'month_year');
                },
                'chargeableMonthlySummariesView' => fn ($view) => $view->createdOn($this->getMonth(), 'chargeable_month'),
            ])
            ->get()
            ->each(function (User $patient) {
                /** @var PatientMonthlySummary */
                $pms = $patient->patientSummaries->first();

                if ( ! $pms) {
                    return;
                }

                if ($patient->chargeableMonthlySummariesView->isEmpty()) {
                    $this->idsToInvestigate[] = $patient->id;

                    return;
                }

                $pms->allChargeableServices->each(function (ChargeableService $cs) use ($patient) {
                    /** @var ChargeablePatientMonthlySummaryView */
                    $csSummary = $patient->chargeableMonthlySummariesView->firstWhere('chargeable_service_id', $cs->id);

                    if (is_null($csSummary)) {
                        $this->idsToInvestigate[] = $patient->id;

                        return;
                    }

                    if ($cs->pivot->is_fulfilled !== $csSummary->is_fulfilled) {
                        $this->idsToInvestigate[] = $patient->id;

                        return;
                    }
                });

                if ($pms->total_time !== $patient->chargeableMonthlySummariesView->sum('total_time')) {
                    $this->idsToInvestigate[] = $patient->id;

                    return;
                }

                if ($pms->no_of_calls !== $patient->chargeableMonthlySummariesView->first()->no_of_calls) {
                    $this->idsToInvestigate[] = $patient->id;

                    return;
                }
            });

        $env = app()->environment();
        if (empty($this->idsToInvestigate)) {
            sendSlackMessage('#billing_alerts', "ENV: {$env}. No issues found while comparing billing data for Practice: {$this->practiceId}", true);

            return;
        }

        $ids = collect($this->idsToInvestigate)->unique()->implode(',');
        sendSlackMessage('#billing_alerts', "ENV: {$env}. Legacy and Revamped billing data for the following patients of Practice: {$this->practiceId} do not match. Please Investigate: {$ids}", true);
    }
}
