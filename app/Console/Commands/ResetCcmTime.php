<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
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
                    PatientMonthlySummary::createFromPatient($patient->id, Carbon::now()->startOfMonth());
                }
            });

        AppConfig::updateOrCreate(
            [
                'config_key' => 'add_new_patient_monthly_summary_record',
            ],
            [
                'config_value' => Carbon::now(),
            ]
        );

        $this->info('CCM Time reset.');
    }
}
