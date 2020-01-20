<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Core\Entities\AppConfig;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use Illuminate\Console\Command;

class ResetCcmTime extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets CCM time for all patients.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:ccm_time';

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
        Patient::withTrashed()
            ->whereDoesntHave('user.patientSummaries', function ($q) {
                $q->where('month_year', '=', Carbon::now()->startOfMonth());
            })
            ->chunk(200, function ($patients) {
                foreach ($patients as $patient) {
                    $summary = PatientMonthlySummary::where('patient_id', '=', $patient->user_id)
                        ->orderBy('id', 'desc')->first();

                    //if we have already summary for this month, then we skip this
                    if ($summary && Carbon::today()->isSameMonth($summary->month_year)) {
                        return;
                    }

                    if ($summary) {
                        //clone record
                        $newSummary = $summary->replicate();
                    } else {
                        $newSummary = new PatientMonthlySummary();
                        $newSummary->patient_id = $patient->user_id;
                    }

                    $newSummary->month_year = Carbon::today()->startOfMonth();
                    $newSummary->total_time = 0;
                    $newSummary->ccm_time = 0;
                    $newSummary->bhi_time = 0;
                    $newSummary->no_of_calls = 0;
                    $newSummary->no_of_successful_calls = 0;
                    $newSummary->approved = 0;
                    $newSummary->rejected = 0;
                    $newSummary->actor_id = null;
                    $newSummary->needs_qa = null;
                    $newSummary->save();
                }
            });

        AppConfig::updateOrCreate([
            'config_key'   => 'add_new_patient_monthly_summary_record',
            'config_value' => Carbon::now(),
        ]);

        $this->info('CCM Time reset.');
    }
}
