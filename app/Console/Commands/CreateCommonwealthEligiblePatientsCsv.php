<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Exports\CommonwealthPcmEligibleExport;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;

class CreateCommonwealthEligiblePatientsCsv extends CreatePracticeReportForUser
{
    use DryRunnable;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a list of all patients with all problems for a user. Returns signed link that expires in 2 days.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'reports:CreateCommonwealthEligiblePatientsCsv';
    
    /**
     * CreatePatientProblemsReportForPractice constructor.
     *
     * @param CommonwealthPcmEligibleExport $report
     */
    public function __construct(CommonwealthPcmEligibleExport $report)
    {
        parent::__construct();
        $this->report = $report;
    }
}
