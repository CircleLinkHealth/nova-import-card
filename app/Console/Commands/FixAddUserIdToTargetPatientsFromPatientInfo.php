<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use Illuminate\Console\Command;

class FixAddUserIdToTargetPatientsFromPatientInfo extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set target_patients.user_id from patient info';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:target_patients:user_id_from_patient_info {minId=1}';

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
        User::withTrashed()
            ->where('id', '>=', $this->argument('minId'))
            ->with('patientInfo')
            ->whereHas('patientInfo', function ($q) {
                $q->whereNotNull('mrn_number');
            })
            ->ofPractice([232, 21, 110, 159, 172, 180, 221])
            ->chunkById(500, function ($users) {
                foreach ($users as $user) {
                    $this->warn("Processing User[$user->id]");
                    $tP = TargetPatient::where([
                        ['practice_id', '=', $user->program_id],
                        ['ehr_patient_id', '=', $user->patientInfo->mrn_number],
                        ['ehr_patient_id', 'is not', null],
                        ['user_id', '!=', $user->id],
                    ])->first();

                    if ($tP) {
                        $this->line("Matching TargetPatient[$tP->id] with User[$user->id]");
                        $tP->user_id = $user->id;
                        $tP->save();
                    }
                }
            });
    }
}
