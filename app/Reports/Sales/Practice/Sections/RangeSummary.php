<?php namespace App\Reports\Sales\Practice\Sections;

use App\Practice;
use App\Reports\Sales\Practice\PracticeStatsHelper;
use App\Reports\Sales\SalesReportSection;
use Carbon\Carbon;

class RangeSummary extends SalesReportSection
{

    private $practice;
    private $service;

    public function __construct(
        Practice $practice,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($practice, $start, $end);
        $this->service = (new PracticeStatsHelper($practice, $start, $end));
        $this->practice = $practice;

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
            'link_to_notes_listing'           => $this->service->linkToPracticeNotes(),
        ];

    }
}