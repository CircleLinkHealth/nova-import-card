<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Exports\PatientProblemsReport;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class CreatePatientProblemsReportForPractice extends Command
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
     * @var PatientProblemsReport
     */
    protected $patientProblemsReport;

    /**
     * CreatePatientProblemsReportForPractice constructor.
     *
     * @param PatientProblemsReport $patientProblemsReport
     */
    public function __construct(PatientProblemsReport $patientProblemsReport)
    {
        parent::__construct();
        $this->patientProblemsReport = $patientProblemsReport;
    }

    public function getArguments()
    {
        return [
            ['practice_id', InputArgument::REQUIRED, 'The practice ID.'],
            ['user_id', InputArgument::REQUIRED, 'The user ID who will have access to download this report.'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $report = $this->patientProblemsReport
            ->forPractice($this->argument('practice_id'))
            ->forUser($this->argument('user_id'))
            ->createMedia()
            ->notifyUser();

        $this->line('Command ran.');
    }
}
