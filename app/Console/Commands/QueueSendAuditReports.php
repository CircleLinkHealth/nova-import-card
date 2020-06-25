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
    protected $signature = 'send:audit-reports {practiceId?} {month?} {limit?} {--dry}';

    private static $dispatched = 0;

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
        if ($inputDate = $this->argument('month')) {
            $date = Carbon::createFromFormat('Y-m-d', $inputDate)->firstOfMonth();
        } else {
            $date = Carbon::now()->subMonth()->firstOfMonth();
        }

        $this->warn("Creating Audit Reports for {$date->toDateString()}");

        User::ofType('participant')
            ->with('patientInfo')
            ->with('patientSummaries')
            ->with('primaryPractice')
            ->with('primaryPractice.settings')
            ->doesntHave('primaryPractice.notificationContactPreferences')
            ->whereHas('primaryPractice', function ($query) {
                $query->where('active', '=', true)
                    ->whereHas('settings', function ($query) {
                        $query->where('dm_audit_reports', '=', true)
                            ->orWhere('efax_audit_reports', '=', true);
                    })->when($this->argument('practiceId'), function ($q) {
                        $q->where('id', '=', $this->argument('practiceId'));
                    });
            })
            ->whereHas('patientSummaries', function ($query) use ($date) {
                $query->where('total_time', '>', 0)
                    ->where('month_year', $date->toDateString());
            })
            ->chunkById(100, function ($patients) use ($date) {
                foreach ($patients as $patient) {
                    $this->warn("Creating audit report for $patient->id");

                    if ( ! $this->option('dry')) {
                        MakeAndDispatchAuditReports::dispatch($patient, $date, true, (bool) $patient->primaryPractice->cpmSettings()->batch_efax_audit_reports)
                            ->onQueue('high');
                    }

                    if ( ! is_null($this->argument('limit')) && ++self::$dispatched >= (int) $this->argument('limit')) {
                        $this->warn('Existing after dispatching '.self::$dispatched.' jobs.');

                        return false;
                    }
                }
            });

        $this->line('Command Finished');
    }
}
