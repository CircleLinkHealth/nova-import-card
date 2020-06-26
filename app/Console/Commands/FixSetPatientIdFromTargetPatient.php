<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use Illuminate\Console\Command;

class FixSetPatientIdFromTargetPatient extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set ccdas.patient_id from target patients';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:ccdas:patient_id_from_target_patients {minId=1}';

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
        TargetPatient::with('ccda')
            ->where('id', '>=', $this->argument('minId'))
            ->has('ccda')
            ->orderBy('id')
            ->chunkById(500, function ($tPs) {
                foreach ($tPs as $tP) {
                    $this->warn("Processing TargetPatient[$tP->id]");

                    if ($tP->ccda->practice_id != $tP->practice_id) {
                        $this->line("Setting practice TargetPatient[$tP->id]");
                        $tP->ccda->practice_id = $tP->practice_id;
                    }
                    if ($tP->ccda->patient_id != $tP->user_id) {
                        $this->line("Setting patient id TargetPatient[$tP->id]");
                        $tP->ccda->patient_id = $tP->user_id;
                    }
                    if ($tP->ccda->isDirty()) {
                        $this->line("Saving TargetPatient[$tP->id]");

                        $tP->ccda->save();

                        continue;
                    }

                    $this->line("Nothing changed TargetPatient[$tP->id]");
                }
            });
    }
}
