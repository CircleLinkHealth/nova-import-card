<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console;

use CircleLinkHealth\Eligibility\Notifications\PatientNotReimportedNotification;
use CircleLinkHealth\Eligibility\Notifications\PatientReimportedNotification;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Decorators\DemographicsFromAthena;
use CircleLinkHealth\Eligibility\Decorators\InsuranceFromAthena;
use CircleLinkHealth\Eligibility\Decorators\MedicalHistoryFromAthena;
use CircleLinkHealth\Eligibility\Decorators\PcmChargeableServices;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;
use CircleLinkHealth\Eligibility\MedicalRecord\MedicalRecordFactory;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CcdaMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CommonwealthMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CsvWithJsonMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\CarePlanHelper;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\AllergyImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\InsuranceLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicationImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicationLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog;
use CircleLinkHealth\SharedModels\Entities\Allergy;
use CircleLinkHealth\SharedModels\Entities\AllergyLog;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy;
use CircleLinkHealth\SharedModels\Entities\Medication;
use CircleLinkHealth\SharedModels\Entities\Problem;
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
    protected $signature = 'patient:recreate {patientUserId} {initiatorUserId?} {--flush-ccd}';
    private   $ccda;
    /**
     * @var Enrollee
     */
    private $enrollee;
    
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
        
        \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id}");
        
        if ($this->option('flush-ccd')) {
            $this->warn('Clearing CCDA data.');
            \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id}:clearing_ccds:ln:".__LINE__);
            $this->clearImportRecordsFor();
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
    
    private function attemptDecorator(User $user, Ccda $ccda)
    {
        if ($mr = MedicalRecordFactory::create($user, $ccda)) {
            $this->warn("Running '{$user->primaryPractice->name}' decorator");
            
            
            return $mr;
        }
        
        return null;
    }
    
    private function attemptFetchCcda(User $user)
    {
        if ($ccda = $this->getUser()->latestCcda()) {
            \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} Fetched latest CCDA ccda_id:{$ccda->id}:ln:".__LINE__);
    
            return $ccda;
        }
        
        $this->correctMrnIfWrong($user);
        
        if ($ccda = $this->getCcdaFromMrn($user->patientInfo->mrn_number, $user->program_id)) {
            return $ccda;
        }
        
        if ($ccda = $this->getCcdaFromAthenaAPI($user)) {
            return $ccda;
        }
    }
    
    private function attemptTemplate(User $user)
    {
        if (in_array($user->primaryPractice->name, ['marillac-clinic-inc', 'calvary-medical-clinic'])) {
            $this->warn("ReimportPatientMedicalRecord:user_id:{$user->id}:enrollee_id:{$this->getEnrollee($user)->id} Running 'csv-with-json' decorator:ln:".__LINE__);
            \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id}:enrollee_id:{$this->getEnrollee($user)->id} Running 'csv-with-json' decorator:ln:".__LINE__);
    
    
            $mr = new CsvWithJsonMedicalRecord(
                tap(
                    sanitize_array_keys($this->getEnrollee($user)->eligibilityJob->data),
                    function ($data) use ($user) {
                        $this->getEnrollee($user)->eligibilityJob->data = $data;
                        $this->getEnrollee($user)->eligibilityJob->save();
                    }
                )
            );
    
            $mrn = $user->patientInfo->mrn_number ?? $this->getEnrollee(
                    $user
                )->eligibilityJob->data['mrn'] ?? null;
            
            if ($mrn) {
                $ccda = $this->getCcdaFromMrn($user->patientInfo->mrn_number, $user->program_id);
            }
            
            if (empty($ccda ?? null)) {
                $ccda = Ccda::create(
                    [
                        'source'      => $mr->getType(),
                        'json'        => $mr->toJson(),
                        'practice_id' => (int) $user->program_id,
                        'patient_id'  => $user->id,
                        'mrn'         => $mrn,
                    ]
                );
                \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} Created CCDA ccda_id:{$ccda->id}:ln:".__LINE__);
            }
        }
        
        return null;
    }
    
    private function clearImportRecordsFor()
    {
        $u = $this->getUser()->load(['ccdas']);
        
        $userId = $u->id;
        
        $u->ccdas->each(
            function ($ccda) {
                \Log::debug("ReimportPatientMedicalRecord:user_id:{$ccda->patient_id}:clearing_ccds:ccda_id:{$ccda->id}");
    
                $class = get_class($ccda);
                
                ProblemImport::where('medical_record_id', '=', $ccda->id)
                             ->where('medical_record_type', '=', $class)
                             ->delete();
                
                ProblemLog::where('medical_record_id', '=', $ccda->id)
                          ->where('medical_record_type', '=', $class)
                          ->delete();
                
                MedicationImport::where('medical_record_id', '=', $ccda->id)
                                ->where('medical_record_type', '=', $class)
                                ->delete();
                
                MedicationLog::where('medical_record_id', '=', $ccda->id)
                             ->where('medical_record_type', '=', $class)
                             ->delete();
                
                AllergyImport::where('medical_record_id', '=', $ccda->id)
                             ->where('medical_record_type', '=', $class)
                             ->delete();
                
                AllergyLog::where('medical_record_id', '=', $ccda->id)
                          ->where('medical_record_type', '=', $class)
                          ->delete();
                
                InsuranceLog::where('medical_record_id', '=', $ccda->id)
                            ->where('medical_record_type', '=', $class)
                            ->delete();
                
                CcdInsurancePolicy::where('medical_record_id', '=', $ccda->id)
                                  ->where('medical_record_type', '=', $class)
                                  ->delete();
    
                ImportedMedicalRecord::where('medical_record_id', '=', $ccda->id)
                                  ->where('medical_record_type', '=', $class)
                                  ->delete();
            }
        );
        
        Problem::where('patient_id', '=', $userId)
               ->delete();
        
        Medication::where('patient_id', '=', $userId)
                  ->delete();
        
        Allergy::where('patient_id', '=', $userId)
               ->delete();
        
        CcdInsurancePolicy::where('patient_id', '=', $userId)
                          ->delete();
    
        ImportedMedicalRecord::where('patient_id', '=', $userId)
                             ->delete();
    }
    
    private function correctMrnIfWrong(User $user)
    {
        if (empty($user->patientInfo->mrn_number) && ! empty($this->getEnrollee($user)->mrn)) {
            \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} Saving mrn from enrollee_id:{$this->getEnrollee($user)->id}:ln:".__LINE__);
    
            $user->patientInfo->mrn_number = $this->getEnrollee($user)->mrn;
            $user->patientInfo->save();
        }
        
        if ($user->patientInfo->mrn_number !== $this->getEnrollee($user)->mrn) {
            if (
                ($this->getEnrollee($user)->first_name == $user->first_name)
                && ($this->getEnrollee($user)->last_name == $user->last_name)
                && ($this->getEnrollee($user)->dob->isSameAs($user->patientInfo->birth_date))
            ) {
                \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} Saving mrn from enrollee_id:{$this->getEnrollee($user)->id}:ln:".__LINE__);
    
                $user->patientInfo->mrn_number = $this->getEnrollee($user)->mrn;
                $user->patientInfo->save();
            }
        }
    }
    
    /**
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
    
    private function getEnrollee(User $user): Enrollee
    {
        if ( ! $this->enrollee) {
            \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} Fetching enrollee ln:".__LINE__);
    
            $this->enrollee = Enrollee::where([
                ['user_id', '=', $user->id],
                ['practice_id', '=', $user->program_id],
                ['first_name', '=', $user->first_name],
                ['last_name', '=', $user->last_name],
                                              ])->with(
                'eligibilityJob'
            )->has('eligibilityJob')->orderByDesc('id')->firstOrFail();
            \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} Fetched enrollee_id:{$this->enrollee->id}:ln:".__LINE__);
        }
        
        return $this->enrollee;
    }
    
    private function getUser()
    {
        return User::with(
            [
                'patientInfo',
                'primaryPractice',
            ]
        )->find($this->argument('patientUserId'));
    }
    
    private function importCcdaAndFillCarePlan(Ccda $ccda, User $user)
    {
        $this->warn("ReimportPatientMedicalRecord:user_id:{$user->id} Importing CCDA:{$ccda->id}:ln:".__LINE__);
        \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} Importing CCDA:{$ccda->id}:ln:".__LINE__);
    
        $ccda->import();
        
        /**
         * @todo: method below is inefficient. Needs to be optimized.
         */
        /** @var ImportedMedicalRecord $imr */
        $imr = $ccda->importedMedicalRecord();
        
        if ( ! $imr) {
            $this->warn("ReimportPatientMedicalRecord:user_id:{$user->id} Creating IMR for CCDA:{$ccda->id}:ln:".__LINE__);
            \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} Creating IMR for CCDA:{$ccda->id}:ln:".__LINE__);
    
            $imr = $ccda->createImportedMedicalRecord()->importedMedicalRecord();
        }
    
        \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} ImportedMedicalRecord_id:{$imr->id}:{$ccda->id}:ln:".__LINE__);
    
    
        $imr->patient_id = $user->id;
        $imr->save();
        
        $this->warn("ReimportPatientMedicalRecord:user_id:{$user->id} Creating CarePlan for CCDA:{$ccda->id}:ln:".__LINE__);
        \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} Creating CarePlan for CCDA:{$ccda->id}:ln:".__LINE__);
    
    
        $imr->updateOrCreateCarePlan();
        
        $this->line("Patient $user->id reimported from CCDA $ccda->id");
        \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} Created CarePlan for CCDA:{$ccda->id}:ln:".__LINE__);
    
    
        $this->getEnrollee($user)->medical_record_id   = $ccda->id;
        $this->getEnrollee($user)->medical_record_type = get_class($ccda);
        $this->getEnrollee($user)->save();
    }
    
    private function notifyFailure(User $user)
    {
        $this->warn("Could not find any records for user:{$user->id}.");
        \Log::debug("Could not find any records for user:{$user->id}");
    
        if ($initiatorId = $this->argument('initiatorUserId')) {
            $this->warn("Notifying of failure user:$initiatorId");
            User::findOrFail($initiatorId)->notify(new PatientNotReimportedNotification($user->id));
        }
    }
    
    private function notifySuccess(User $user)
    {
        if ($initiatorId = $this->argument('initiatorUserId')) {
            $this->warn("Notifying user:$initiatorId");
            User::findOrFail($initiatorId)->notify(new PatientReimportedNotification($user->id));
        }
    }
}
