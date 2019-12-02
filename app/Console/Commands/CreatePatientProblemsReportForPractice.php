<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Exports\PracticeReports\PatientProblemsReport;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;

class CreatePatientProblemsReportForPractice extends CreatePracticeReportForUser
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
    protected $name = 'reports:all-patient-with-problems';

    /**
     * CreatePatientProblemsReportForPractice constructor.
     *
     * @param PatientProblemsReport $patientProblemsReport
     */
    public function __construct(PatientProblemsReport $patientProblemsReport)
    {
        parent::__construct();
        $this->report = $patientProblemsReport;
    }
}
