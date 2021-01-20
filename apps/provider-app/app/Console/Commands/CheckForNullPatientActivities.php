<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\SharedModels\Entities\Activity;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckForNullPatientActivities extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for null patient activities and send alert in slack';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:null-patient-activities {forMonth?}';

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
     * @return int
     */
    public function handle()
    {
        $month = $this->argument('forMonth');
        if ( ! $month) {
            $month = now()->startOfMonth();
        } else {
            $month = Carbon::parse($month);
        }

        $patientIds = Activity::where('performed_at', '>', $month)
            ->whereNull('chargeable_service_id')
            ->select('patient_id')
            ->distinct()
            ->pluck('patient_id')
            ->toArray();

        $patientsStr = implode(', ', $patientIds);
        $msg         = "The following patients have null activities. Please check.\n$patientsStr";

        sendSlackMessage('#time-tracking-issues', $msg);
        $this->info($msg);

        return 0;
    }
}
