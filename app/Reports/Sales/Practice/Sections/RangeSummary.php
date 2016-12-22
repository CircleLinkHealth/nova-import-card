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
        $this->service = (new PracticeStatsHelper($start, $end));
        $this->practice = $practice;

    }

    public function renderSection()
    {

        return $this->data['Overall Summary'] = [
            'no_of_call_attempts'             => $this->service->callCountForPractice($this->practice),
            'no_of_successful_calls'          => $this->service->successfulCallCountForPractice($this->practice),
            'total_ccm_time'                  => $this->service->totalCCMTime($this->practice),
            'no_of_biometric_entries'         => $this->service->numberOfBiometricsRecorded($this->practice),
            'no_of_forwarded_notes'           => $this->service->noteStats($this->practice),
            'no_of_forwarded_emergency_notes' => $this->service->emergencyNotesCount($this->practice),
            'link_to_notes_listing'           => $this->service->linkToPracticeNotes($this->practice)
        ];

    }
}