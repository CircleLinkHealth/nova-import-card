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

        $this->data['historical'] = $this->service->historicalEnrollmentPerformance($this->provider, Carbon::parse($this->start), Carbon::parse($this->end));

        return $this->data;

    }

}