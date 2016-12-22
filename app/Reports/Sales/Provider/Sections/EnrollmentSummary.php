<?php

namespace App\Reports\Sales\Provider\Sections;

use App\PatientInfo;
use App\Reports\Sales\Provider\ProviderStatsHelper;
use App\Reports\Sales\SalesReportSection;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EnrollmentSummary extends SalesReportSection
{

    private $provider;
    private $service;

    public function __construct(
        User $provider,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($provider, $start, $end);
        $this->provider = $provider;
        $this->service = (new ProviderStatsHelper($start, $end));
    }

    public function renderSection()
    {
        $id = $this->provider->id;

        $enrollmentCumulative = PatientInfo::whereHas('user', function ($q) use ($id) {

            $q->hasBillingProvider($id);

        })
            ->whereNotNull('ccm_status')
            ->select(DB::raw('count(ccm_status) as total, ccm_status'))
            ->groupBy('ccm_status')
            ->get()
            ->toArray();

        $this->data['enrolled'] = $enrollmentCumulative[0]['total'] ?? 0;
        $this->data['paused'] = $enrollmentCumulative[1]['total'] ?? 0;
        $this->data['withdrawn'] = $enrollmentCumulative[2]['total'] ?? 0;

        for ($i = 0; $i < 4; $i++) {

            $billable = $this->service->billableCountForMonth($this->provider, Carbon::parse($this->start)->subMonths($i));

            //if first month, do a month-to-date
            if ($i == 0) {

                $month = Carbon::parse($this->start)->format('F Y');
                $this->data['historical'][$month] =
                                            $this->service->enrollmentCountByProvider(
                                            $this->provider,
                                            $this->start,
                                            $this->end);

                $this->data['historical'][$month]['billable'] = $billable;

            } else {

                $iMonthsAgo = Carbon::parse($this->start)->subMonths($i);
                $start = Carbon::parse($iMonthsAgo)->firstOfMonth();
                $end = Carbon::parse($iMonthsAgo)->lastOfMonth();

                $month = Carbon::parse($iMonthsAgo)->format('F Y');
                $this->data['historical'][$month] = $this->service->enrollmentCountByProvider($this->provider,
                    $start, $end);

                $this->data['historical'][$month]['billable'] = $billable;
            }

        }

        return $this->data;

    }

}