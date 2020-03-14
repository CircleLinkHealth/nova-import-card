<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\PatientNotReimportedNotification;
use App\Notifications\PatientReimportedNotification;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Decorators\InsuranceFromAthena;
use CircleLinkHealth\Eligibility\Decorators\MedicalHistoryFromAthena;
use CircleLinkHealth\Eligibility\Decorators\PcmChargeableServices;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CcdaMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\CarePlanHelper;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CommonwealthMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\MarillacMedicalRecord;
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
     * @var Enrollee
     */
    private $enrollee;
    private $ccda;
    
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
        $user = $this->getUser();
        
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
        
        $this->notifyFailure($user);
    }
    
    private function attemptCcda(User $user)
    {
        $ccda = $this->attemptFetchCcda($user);
        
        if ( ! $ccda) {
            return false;
        }
        
        if ($mr = $this->attemptDecorator($user, $ccda)) {
            if ( ! is_null($mr)) {
                $ccda->json = $mr->toJson();
                $ccda->save();
            }
        }
        
        $this->importCcdaAndFillCarePlan($ccda, $user);
        
        $this->notifySuccess($user);
        
        return true;
    }
    
    private function attemptTemplate(User $user)
    {
        if ('marillac-clinic-inc' === $user->primaryPractice->name) {
            $this->warn("Running 'marillac-clinic-inc' decorator");
            
            $mr = new MarillacMedicalRecord(
                $this->getEnrollee($user)->eligibilityJob->data
            );
            
            $ccda = $this->getCcdaFromMrn($user->getMRN(), $user->program_id);
            
            if ( ! $ccda) {
                $ccda = Ccda::create(
                    [
                        'source'      => $mr->getType(),
                        'json'        => $mr->toJson(),
                        'practice_id' => (int) $user->program_id,
                    ]
                );
            }
        }
        
        return null;
    }
    
    private function attemptDecorator(User $user, Ccda $ccda)
    {
        if ('commonwealth-pain-associates-pllc' === $user->primaryPractice->name) {
            $this->warn("Running 'commonwealth-pain-associates-pllc' decorator");
            
            return new CommonwealthMedicalRecord(
                app(PcmChargeableServices::class)->decorate(
                    app(MedicalHistoryFromAthena::class)->decorate(
                        app(InsuranceFromAthena::class)->decorate(
                            $this->getEnrollee($user)->eligibilityJob
                        )
                    )
                )->data,
                new CcdaMedicalRecord($ccda->bluebuttonJson())
            );
        }
        
        return null;
    }
    
    private function getEnrollee(User $user): Enrollee
    {
        if ( ! $this->enrollee) {
            $this->enrollee = Enrollee::whereUserId($user->id)->wherePracticeId($user->program_id)->with(
                'eligibilityJob'
            )->has('eligibilityJob')->first();
        }
        
        return $this->enrollee;
    }
    
    private function importCcdaAndFillCarePlan($ccda, User $user)
    {
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
        
        $this->getEnrollee($user)->medical_record_id   = $ccda->id;
        $this->getEnrollee($user)->medical_record_type = get_class($ccda);
        $this->getEnrollee($user)->save();
    }
    
    private function notifySuccess(User $user)
    {
        if ($initiatorId = $this->argument('initiatorUserId')) {
            $this->warn("Notifying user:$initiatorId");
            User::findOrFail($initiatorId)->notify(new PatientReimportedNotification($user->id));
        }
    }
    
    private function notifyFailure(User $user)
    {
        if ($initiatorId = $this->argument('initiatorUserId')) {
            $this->warn("Notifying of failure user:$initiatorId");
            User::findOrFail($initiatorId)->notify(new PatientNotReimportedNotification($user->id));
        }
    }
    
    private function getUser()
    {
        return User::with(
            [
                'patientInfo',
            ]
        )->find($this->argument('patientUserId'));
    }
    
    private function getCcdaFromMrn($mrn, int $practiceId)
    {
        if ( ! $mrn || ! $practiceId) {
            return null;
        }
        
        if ( ! $this->ccda) {
            $this->ccda = Ccda::where('practice_id', $practiceId)->where(
                function ($q) use ($mrn) {
                    $q->where('patient_id', $this->argument('patientUserId'))
                      ->orWhere('mrn', $mrn);
                }
            )->first();
        }
        
        return $this->ccda;
    }
    
    private function attemptFetchCcda(User $user)
    {
        if ($ccda = $this->getCcdaFromMrn($user->getMRN(), $user->program_id)) {
            return $ccda;
        }
        
        if ($ccda = $this->getCcdaFromAthenaAPI($user)) {
            return $ccda;
        }
    }
    
    /**
     * @param User $user
     *
     * @return MedicalRecord|null
     * @throws \Exception
     */
    private function getCcdaFromAthenaAPI(User $user): ?MedicalRecord
    {
        $user->loadMissing('ehrInfo');
        
        if ( ! $user->ehrInfo) {
            return null;
        }
        
        $this->warn("Fetching CCDA from AthenaAPI for user:$user->id");
        
        return AthenaEligibilityCheckableFactory::getCCDFromAthenaApi($user->ehrInfo);
    }
}
