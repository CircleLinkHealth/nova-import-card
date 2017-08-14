<?php

namespace App\Reports\Sales\Provider\Sections;

use App\Reports\Sales\Provider\ProviderStatsHelper;
use App\Reports\Sales\SalesReportSection;
use App\User;
use Carbon\Carbon;

class RangeSummary extends SalesReportSection
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
        $this->service = (new ProviderStatsHelper($provider, $start, $end));
    }

    public function renderSection()
    {
        return $this->data['Overall Summary'] = [
            'no_of_call_attempts'             => $this->service->callCount(),
            'no_of_successful_calls'          => $this->service->successfulCallCount(),
            'total_ccm_time'                  => $this->service->totalCCMTimeHours(),
            'no_of_biometric_entries'         => $this->service->numberOfBiometricsRecorded(),
            'no_of_forwarded_notes'           => $this->service->noteStats(),
            'no_of_forwarded_emergency_notes' => $this->service->emergencyNotesCount(),
            'link_to_notes_listing'           => $this->service->linkToProviderNotes(),
        ];
    }

}