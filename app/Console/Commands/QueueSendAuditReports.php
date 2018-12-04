<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\MakeAndDispatchAuditReports;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class QueueSendAuditReports extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends audit reports to practices that choose to receive them via eFax or DM.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:audit-reports';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = Carbon::now()->subMonth()->firstOfMonth();

        $patients = User::ofType('participant')
            ->with('patientInfo')
            ->with('patientSummaries')
            ->with('primaryPractice')
            ->with('primaryPractice.settings')
            ->whereHas('primaryPractice', function ($query) {
                $query->where('active', '=', true)
                                ->whereHas('settings', function ($query) {
                                    $query->where('dm_audit_reports', '=', true)
                                          ->orWhere('efax_audit_reports', '=', true);
                                });
            })
            ->whereHas('patientSummaries', function ($query) use ($date) {
                $query->where('total_time', '>', 0)
                                ->where('month_year', $date->toDateString());
            })
            ->get();

        foreach ($patients as $patient) {
            $job = (new MakeAndDispatchAuditReports($patient, $date))
                ->onQueue('high');

            dispatch($job);
        }
    }
}
