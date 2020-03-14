<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\PatientReimportedNotification;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\CarePlanHelper;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use CircleLinkHealth\Eligibility\Templates\CommonwealthMedicalRecord;
use CircleLinkHealth\Eligibility\Templates\MarillacMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Console\Command;

class ReimportPatientMedicalRecord extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reimport patient data from one medical record decided by this command. To be used for patient that did not import correctly.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patient:recreate {patientUserId} {initiatorUserId?}';

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
        $user = User::with([
            'patientInfo',
        ])->find($this->argument('patientUserId'));

        if ( ! $user) {
            $this->error('User not found');

            return;
        }
    
        if ($this->attemptTemplate($user)) {
            return;
        }

        if ($this->attemptCcda($user)) {
            return;
        }
    }

    private function attemptCcda(User $user)
    {
        $ccda = Ccda::withTrashed()->wherePracticeId($user->program_id)->where('json->demographics->mrn_number', $user->getMRN())->first();

        if ( ! $ccda) {
            return false;
        }

        if ($mr = $this->attemptDecorator($user, $ccda)) {
            if ( ! is_null($mr)) {
                $ccda->json = $mr->toJson();
                $ccda->save();
            }
        }

        $this->warn("Importing CCDA:$ccda->id");
        $ccda->import();

        /**
         * @todo: method below is inefficient. Needs to be optimized.
         */
        /** @var ImportedMedicalRecord $imr */
        $imr = $ccda->importedMedicalRecord();

        if ( ! $imr) {
            $this->warn("Creating IMR for CCDA:$ccda->id");
            $imr = $ccda->createImportedMedicalRecord()->importedMedicalRecord();
        }

        $imr->patient_id = $user->id;
        $imr->save();

        $this->warn("Creating CarePlan from CCDA:$ccda->id");
        
        $imr->updateOrCreateCarePlan();
        
        $this->line("Patient $user->id reimported from CCDA $ccda->id");
    
        if ($initiatorId = $this->argument('initiatorUserId')) {
            $this->warn("Notifying user:$initiatorId");
            User::firstOrFail($initiatorId)->notify(new PatientReimportedNotification($user->id));
        }

        return true;
    }
    
    private function attemptTemplate(User $user)
    {
        if ('marillac-clinic-inc' === $user->primaryPractice->name) {
            $this->warn("Running 'marillac-clinic-inc' decorator");
            
            $mr = new MarillacMedicalRecord(Enrollee::whereUserId($user->id)->with('eligibilityJob')->has('eligibilityJob')->first()->eligibilityJob->data);
        }
        
        return null;
    }

    private function attemptDecorator(User $user, Ccda $ccda)
    {
        if ('commonwealth-pain-associates-pllc' === $user->primaryPractice->name) {
            $this->warn("Running 'commonwealth-pain-associates-pllc' decorator");

            return new CommonwealthMedicalRecord(Enrollee::whereUserId($user->id)->with('eligibilityJob')->has('eligibilityJob')->first()->eligibilityJob->data, $ccda->bluebuttonJson());
        }
        
        return null;
    }
}
