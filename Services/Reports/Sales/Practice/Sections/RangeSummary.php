<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services\Reports\Sales\Practice\Sections;

use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\PracticeReportable;
use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\SalesReportSection;
use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\StatsHelper;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;

class RangeSummary extends SalesReportSection
{
    protected $service;

    public function __construct(
        Practice $practice,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($practice, $start, $end);
        $this->service = new StatsHelper(new PracticeReportable($practice));
    }

    public function render()
    {
        return $this->data['Overall Summary'] = [
            'no_of_call_attempts'             => $this->service->callCount($this->start, $this->end),
            'no_of_successful_calls'          => $this->service->successfulCallCount($this->start, $this->end),
            'total_ccm_time'                  => $this->service->totalCCMTimeHours($this->start, $this->end),
            'no_of_biometric_entries'         => $this->service->numberOfBiometricsRecorded($this->start, $this->end),
            'no_of_forwarded_notes'           => $this->service->noteStats($this->start, $this->end),
            'no_of_forwarded_emergency_notes' => $this->service->emergencyNotesCount($this->start, $this->end),
            'link_to_notes_listing'           => $this->service->linkToNotes(),
        ];
    }
}
