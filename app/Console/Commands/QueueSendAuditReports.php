<?php

namespace App\Console\Commands;

use App\Jobs\MakeAndDispatchAuditReports;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class QueueSendAuditReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:audit-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends audit reports to practices that choose to receive them via eFax or DM.';

    /**
     * Create a new command instance.
     *
     * @return void
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
        $patients = User::ofType('participant')
            ->with('patientInfo')
            ->with('patientInfo.monthlySummaries')
            ->with('primaryPractice')
            ->with('primaryPractice.settings')
            ->whereHas('primaryPractice', function ($query) {
                $query->where('active', '=', true)
                    ->whereHas('settings', function ($query) {
                        $query->where('dm_audit_reports', '=', true)
                            ->orWhere('efax_audit_reports', '=', true);
                    });
            })
            ->whereHas('patientInfo.monthlySummaries', function ($query) {
                $query->where('ccm_time', '>', 0)
                    ->where('month_year', Carbon::now()->subMonth()->firstOfMonth()->toDateString());
            })
            ->get();

        foreach ($patients as $patient) {
            $job = (new MakeAndDispatchAuditReports($patient))
                ->onQueue('reports');

            dispatch($job);
        }
    }
}
