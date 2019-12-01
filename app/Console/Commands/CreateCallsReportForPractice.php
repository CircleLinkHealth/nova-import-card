<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Exports\PracticeCallsReport;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;

class CreateCallsReportForPractice extends CreatePracticeReportForUser
{
    use DryRunnable;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a list of all practice calls for the last 3 months.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'reports:practice-calls-last-three-months';

    /**
     * CreatePatientProblemsReportForPractice constructor.
     */
    public function __construct(PracticeCallsReport $report)
    {
        parent::__construct();
        $this->report = $report;
    }
}
