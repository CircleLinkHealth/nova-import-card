<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\User;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use Illuminate\Console\Command;

class FixTargetPatientUserIds extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes sure target patietns match practice id and mrn of their related user_id';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:target_patients';

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
        TargetPatient::with('user.patientInfo')->has('user.patientInfo')->chunkById(500, function ($tPs) {
            foreach ($tPs as $tP) {
                $this->warn("Processing TargetPatient[$tP->id]");
                if (in_array($tP->user->program_id, [232, 21, 110, 159, 172, 180, 221])
                    && $tP->user->program_id == $tP->practice_id
                    && is_numeric($tP->user->patientInfo->mrn_number)
                    && $tP->user->patientInfo->mrn_number == $tP->ehr_patient_id
                ) {
                    continue;
                }

                $u = User::ofType('participant')
                    ->ofPractice($tP->practice_id)
                    ->whereHas('patientInfo', function ($q) use ($tP) {
                        $q->whereNotNull('mrn_number')->where('mrn_number', $tP->ehr_patient_id);
                    })->first();

                if ($u && $tP->ccda->patient_last_name === $u->last_name) {
                    $this->line("User matching CCD found TargetPatient[$tP->id]");
                    $tP->user_id = $u->id;
                    $tP->save();
                    continue;
                }

                if ( ! in_array($tP->user->program_id, [232, 21, 110, 159, 172, 180, 221])) {
                    $this->line("Does not belong to athena practice. Set null. TargetPatient[$tP->id]");
                    $tP->user_id = null;
                    $tP->save();
                    continue;
                }

                if (TargetPatient::STATUS_INELIGIBLE == $tP->status) {
                    $this->line("Is ineligible. Set null. TargetPatient[$tP->id]");
                    $tP->user_id = null;
                    $tP->enrollee_id = null;
                    $tP->save();
                    continue;
                }
            }
        });
    }
}
