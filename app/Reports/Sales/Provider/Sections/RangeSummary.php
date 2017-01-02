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
        $this->service = (new ProviderStatsHelper($start, $end));
        $this->provider = $provider;
    }

    public function renderSection()
    {

        return $this->data['Overall Summary'] = [
            'no_of_call_attempts'             => $this->service->callCountForProvider($this->provider),
            'no_of_successful_calls'          => $this->service->successfulCallCountForProvider($this->provider),
            'total_ccm_time'                  => $this->service->totalCCMTime($this->provider),
            'no_of_biometric_entries'         => $this->service->numberOfBiometricsRecorded($this->provider),
            'no_of_forwarded_notes'           => $this->service->noteStats($this->provider),
            'no_of_forwarded_emergency_notes' => $this->service->emergencyNotesCount($this->provider),
            'link_to_notes_listing'           => $this->service->linkToProviderNotes($this->provider),
        ];

    }

}