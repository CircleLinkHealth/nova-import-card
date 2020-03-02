<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\CarePlanHelper;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\Medications;
use Illuminate\Console\Command;

class AddPracticesToSearchDriver extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports data from Practices table to search driver.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:import-practices';

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
        ImportedMedicalRecord::wherePracticeId(228)->with('patient')->has('patient')->chunkById(50, function ($imrs){
            foreach ($imrs as $imr) {
                $this->warn("fixing patient:{$imr->patient->id}");
                $m = (new Medications($imr->medical_record_id, $imr->medical_record_type, $imr))->import($imr->medical_record_id, $imr->medical_record_type, $imr);
                (new CarePlanHelper($imr->patient, $imr))->storeMedications();
            }
        });
    }
}
