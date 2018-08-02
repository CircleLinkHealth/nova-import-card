<?php

namespace App\Console\Commands;

use App\AppConfig;
use App\Patient;
use App\PatientMonthlySummary;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ResetCcmTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:ccm_time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets CCM time for all patients.';

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
        $appConfigs = AppConfig::all();

        $lastReset = $appConfigs->where('config_key', 'cur_month_ccm_time_last_reset')->first();

        /*
        Patient::withTrashed()
            ->update([
                'cur_month_activity_time' => '0',
            ]);
        */

        Patient::withTrashed()
               ->chunk(200, function (Patient $patient) {
                   $patient->cur_month_activity_time = 0;
                   $patient->save();

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
                       $newSummary             = new PatientMonthlySummary();
                       $newSummary->patient_id = $patient->user_id;
                   }

                   $newSummary->month_year             = Carbon::today();
                   $newSummary->total_time             = 0;
                   $newSummary->ccm_time               = 0;
                   $newSummary->bhi_time               = 0;
                   $newSummary->no_of_calls            = 0;
                   $newSummary->no_of_successful_calls = 0;
                   $newSummary->is_ccm_complex         = 0;
                   $newSummary->approved               = 0;
                   $newSummary->rejected               = 0;
                   $newSummary->actor_id               = null;
                   $newSummary->needs_qa               = null;
                   $newSummary->save();
               });

        $lastReset->config_value = Carbon::now();
        $lastReset->save();

        AppConfig::updateOrCreate([
            'config_key'   => 'reset_cur_month_activity_time',
            'config_value' => Carbon::now(),
        ]);

        $this->info('CCM Time reset.');
    }
}
