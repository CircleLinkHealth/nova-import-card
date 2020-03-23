<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\MakeAndDispatchAuditReports;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
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
    protected $signature = 'send:audit-reports {practiceId?} {month?}';

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
        $date = $this->hasArgument('month') ? Carbon::createFromFormat('Y-m-d', $this->argument('month'))->firstOfMonth() : Carbon::now()->subMonth()->firstOfMonth();

        $this->warn("Creating report for {$date->toDateString()}");

        User::ofType('participant')
            ->with('patientInfo')
            ->with('patientSummaries')
            ->with('primaryPractice')
            ->with('primaryPractice.settings')
            ->whereHas('primaryPractice', function ($query) {
                $query->where('active', '=', true)
                    ->whereHas('settings', function ($query) {
                        $query->where('dm_audit_reports', '=', true)
                            ->orWhere('efax_audit_reports', '=', true);
                    })->when($this->hasArgument('practiceId'), function ($q) {
                        $q->where('id', '=', $this->argument('practiceId'));
                    });
            })
            ->whereHas('patientSummaries', function ($query) use ($date) {
                $query->where('total_time', '>', 0)
                    ->where('month_year', $date->toDateString());
            })
            ->chunkById(100, function ($patients) use ($date) {
                $delay = 2;

                foreach ($patients as $patient) {
                    $this->warn("Creating audit report for $patient->id");
                    MakeAndDispatchAuditReports::dispatch($patient, $date)
                        ->onQueue('high')->delay(now()->addSeconds($delay));
                    ++$delay;
                }
            });

        $this->line('Command Finished');
    }
}
