<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Console\ReimportPatientMedicalRecord;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CsvWithJsonMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\ImportService;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class ImportConsentedEnrollees implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;
    /**
     * @var \CircleLinkHealth\Eligibility\Entities\EligibilityBatch
     */
    private $batch;
    /**
     * @var array
     */
    private $enrolleeIds;
    
    /**
     * Create a new job instance.
     */
    public function __construct(array $enrolleeIds, EligibilityBatch $batch = null)
    {
        $this->enrolleeIds = $enrolleeIds;
        $this->batch       = $batch;
    }
    
    /**
     * Execute the job.
     *
     * @param \CircleLinkHealth\Eligibility\ProcessEligibilityService $importService
     */
    public function handle(ImportService $importService)
    {
        Enrollee::whereIn('id', $this->enrolleeIds)
                ->with(['targetPatient', 'practice', 'eligibilityJob'])
                ->chunkById(
                    10,
                    function ($enrollees) use ($importService) {
                        $enrollees->each(
                            function ($enrollee) use ($importService) {
                                //verify it wasn't already imported
                                if ($enrollee->user_id) {
                                    /** @var User|null $handled */
                                    $handled = $this->handleExistingUser($enrollee);
                            
                                    if ( ! is_null($handled)) {
                                        return $handled;
                                    }
                                }
                        
                                //import ccda
                                if ($importService->isCcda($enrollee->medical_record_type)) {
                                    return $importService->importExistingCcda($enrollee->medical_record_id, $enrollee);
                                }
                        
                                //import from AthenaAPI
                                if ($enrollee->targetPatient) {
                                    return $this->importTargetPatient($enrollee);
                                }
                        
                                //import from eligibility jobs
                                $job = $this->eligibilityJob($enrollee);
                        
                                if ($job) {
                                    $importedUsingMrn = $this->importCcdUsingMrnFromEligibilityJob($job, $enrollee);
                            
                                    if (false !== $importedUsingMrn) {
                                        return $importedUsingMrn;
                                    }
                            
                                    return $this->importFromEligibilityJob($enrollee, $job);
                                }
                        
                                throw new \Exception("This should never be reached. enrollee: $enrollee->id");
                            }
                        );
                    }
                );
    }
    
    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        $ids = implode(',', $this->enrolleeIds);
        
        return ['importconsentedenrollees', 'enrollees:'.$ids];
    }
    
    private function eligibilityJob(Enrollee $enrollee)
    {
        if ($enrollee->eligibilityJob) {
            return $enrollee->eligibilityJob;
        }
        $hash = $enrollee->practice->name.$enrollee->first_name.$enrollee->last_name.$enrollee->mrn.$enrollee->city.$enrollee->state.$enrollee->zip;
        
        return EligibilityJob::whereHash($hash)->first();
    }
    
    private function enrolleeAlreadyImported(Enrollee $enrollee)
    {
        $link = route('patient.careplan.print', [$enrollee->user_id]);
        $this->log("Eligible patient with ID {$enrollee->id} has already been imported. See $link");
    }
    
    private function enrolleeMedicalRecordImported(Enrollee $enrollee)
    {
        $link = route('import.ccd.remix');
        $this->log("Just imported the CCD of Eligible Patient ID {$enrollee->id}. Please visit $link");
    }
    
    private function importFromEligibilityJob(Enrollee $enrollee, EligibilityJob $job)
    {
        $ccda = Ccda::create([
            'json' => (new CsvWithJsonMedicalRecord($job->data))->toJson(),
            'practice_id' => $enrollee->practice_id
                             ]);
        
        $enrollee->medical_record_type = get_class($ccda);
        $enrollee->medical_record_id = $ccda->id;
        $enrollee->save();
        
        $ccda = $ccda->import($enrollee);
        
        $this->enrolleeMedicalRecordImported($enrollee);
    }
    
    private function importTargetPatient(Enrollee $enrollee)
    {
        $url = route(
            'import.ccd.remix',
            'Click here to Create and a CarePlan and review.'
        );
        
        $athenaApi = app(\CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation::class);
        
        $ccdaExternal = $athenaApi->getCcd(
            $enrollee->targetPatient->ehr_patient_id,
            $enrollee->targetPatient->ehr_practice_id,
            $enrollee->targetPatient->ehr_department_id
        );
        
        if ( ! isset($ccdaExternal[0])) {
            $this->log("Could not retrieve CCD from Athena for eligible patient id $enrollee->id");
            
            return;
        }
        
        $ccda = Ccda::create(
            [
                'practice_id' => $enrollee->practice_id,
                'vendor_id'   => 1,
                'xml'         => $ccdaExternal[0]['ccda'],
            ]
        );
        
        $enrollee->medical_record_id   = $ccda->id;
        $enrollee->medical_record_type = Ccda::class;
        $imported                      = $ccda->import();
        $enrollee->save();
        
        $this->enrolleeMedicalRecordImported($enrollee);
    }
    
    private function log($message)
    {
        \Log::warning($message);
        
        sendSlackMessage('#parse_enroll_import', $message);
    }
    
    /**
     * @param EligibilityJob $job
     * @param Enrollee $enrollee
     *
     * @return bool|\stdClass
     */
    private function importCcdUsingMrnFromEligibilityJob(EligibilityJob $job, Enrollee $enrollee)
    {
        $mrn = $job->data['mrn_number'] ?? $job->data['mrn'] ?? $job->data['patient_id'] ?? $job->data['internal_id'] ?? null;
        
        if ( ! $mrn) {
            return false;
        }
        
        $ccda = Ccda::whereBatchId($job->batch_id)->whereMrn($mrn)->first();
        
        if ( ! $ccda) {
            return false;
        }
        
        $enrollee->medical_record_id   = $ccda->id;
        $enrollee->medical_record_type = Ccda::class;
        $enrollee->save();
        
        return app(ImportService::class)->importExistingCcda($ccda->id);
    }
    
    private function handleExistingUser(Enrollee $enrollee): ?User
    {
        if ( ! $enrollee->user_id) {
            return null;
        }
        
        $user = User::withTrashed()->find($enrollee->user_id);
        
        if ( ! $user) {
            $enrollee->user_id = null;
            $enrollee->save();
            
            return null;
        };
        
        if (is_null($user->deleted_at)) {
            $this->enrolleeAlreadyImported($enrollee);
            
            return $user;
        }
        
        if ($user->restore()) {
            Artisan::call(
                ReimportPatientMedicalRecord::class,
                [
                    'patientUserId'   => $user->id,
                    'initiatorUserId' => auth()->id(),
                ]
            );
            
            $this->enrolleeMedicalRecordImported($enrollee);
            
            return $user;
        }
    }
}

