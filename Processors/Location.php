<?php


namespace CircleLinkHealth\CcmBilling\Processors;


use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\BillingProcesor;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Location implements BillingProcesor
{
    private $locations;
    
    public function __construct(\CircleLinkHealth\Customer\Entities\Location ...$locations)
    {
        $this->locations = ! ($locations instanceof Collection || $locations instanceof \Illuminate\Database\Eloquent\Collection) ? collect($locations) : $locations;
    }
    
    public function patientBillableServicesQuery(Carbon $monthYear): Builder
    {
        // TODO: Implement patientBillableServicesQuery() method.
    }
    
    public function billablePatientsQuery(Carbon $monthYear): Builder
    {
        return
            User::with([
                'endOfMonthCcmStatusLog' => function ($q) use ($monthYear) {
                    $q->createdOn($monthYear, 'month_year');
                },
                'patientMonthlySummaries' => function ($q) use ($monthYear) {
                    $q->createdOn($monthYear, 'month_year');
                },
                'attestedProblems' => function ($q) use ($monthYear) {
                    $q
                        ->with([
                            'cpmProblem',
                            'icd10Codes',
                        ])
                        ->createdOn($monthYear, 'month_year');
                },
                'billingProvider.user',
                'patientInfo',
                'ccdProblems' => function ($problem) {
                    $problem->with(['cpmProblem', 'codes', 'icd10Codes']);
                },
                'chargeableMonthlySummary' => function ($q) use ($monthYear) {
                    $q->createdOn($monthYear, 'month_year');
                },
            ])->whereHas('patientInfo', fn($q) => $q->whereIn('preferred_contact_location', $this->locations->pluck('id')->all()));
    }
}