<?php

namespace App\Jobs;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Enrollee;
use App\Models\MedicalRecords\Ccda;
use App\Services\AthenaAPI\Calls;
use App\Services\CCD\ProcessEligibilityService;
use App\Services\MedicalRecords\ImportService;
use App\ValueObjects\BlueButtonMedicalRecord\MedicalRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportConsentedEnrollees implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var array
     */
    private $enrolleeIds;
    /**
     * @var EligibilityBatch
     */
    private $batch;

    /**
     * Create a new job instance.
     *
     * @param array $enrolleeIds
     */
    public function __construct(array $enrolleeIds, EligibilityBatch $batch = null)
    {
        $this->enrolleeIds = $enrolleeIds;
        $this->batch       = $batch;
    }

    /**
     * Execute the job.
     *
     * @param ProcessEligibilityService $importService
     *
     * @return void
     */
    public function handle(ImportService $importService)
    {
        $imported = Enrollee::whereIn('id', $this->enrolleeIds)
                            ->with(['targetPatient', 'practice'])
                            ->get()
                            ->map(function ($enrollee) use ($importService) {
                                $url = route('import.ccd.remix',
                                    'Click here to Create and a CarePlan and review.');

                                //verify it wasn't already imported
                                if ($enrollee->user_id) {
                                    return [
                                        'patient' => $enrollee->nameAndDob(),
                                        'message' => 'This patient has already been imported',
                                        'type'    => 'error',
                                    ];
                                }

                                //verify it wasn't already imported
                                $imr = $enrollee->getImportedMedicalRecord();
                                if ($imr) {
                                    if ($imr->patient_id) {
                                        $enrollee->user_id = $imr->patient_id;
                                        $enrollee->save();

                                        return [
                                            'patient' => $enrollee->nameAndDob(),
                                            'message' => 'This patient has already been imported',
                                            'type'    => 'error',
                                        ];
                                    }

                                    return [
                                        'patient' => $enrollee->nameAndDob(),
                                        'message' => "The CCD was imported. $url",
                                        'type'    => 'success',
                                    ];
                                }

                                //import PHX
                                if ($enrollee->practice_id == 139) {
                                    ImportPHXEnrollee::dispatch($enrollee);

                                    return $enrollee;
                                }

                                //import from AthenaAPI
                                if ($enrollee->targetPatient) {
                                    return $this->importTargetPatient($enrollee);
                                }

                                //import from eligibility jobs
                                $job = $this->eligibilityJob($enrollee);
                                if ($job) {
                                    return $this->importFromEligibilityJob($enrollee, $job);
                                }

                                //import ccda
                                if ($importService->isCcda($enrollee->medical_record_type)) {
                                    $response = $importService->importExistingCcda($enrollee->medical_record_id);

                                    if ($response->imr) {
                                        return [
                                            'patient' => $enrollee->nameAndDob(),
                                            'message' => "The CCD was imported. $url",
                                            'type'    => 'success',
                                        ];
                                    }
                                }

                                return [
                                    'patient' => $enrollee->nameAndDob(),
                                    'message' => $response->message ?? 'Sorry. Some random error occured. Please post to #qualityassurance to notify everyone to stop using the importer, and also tag Michalis to fix this asap.',
                                    'type'    => 'error',
                                ];
                            });

        if ($this->batch && $imported->isNotEmpty()) {
            \Log::info($imported->toJson());

            \Cache::put("batch:{$this->batch->id}:last_consented_enrollee_import", $imported->toJson(), 14400);
        }

        return $imported;
    }

    private function importTargetPatient(Enrollee $enrollee)
    {
        $url = route('import.ccd.remix',
            'Click here to Create and a CarePlan and review.');

        $athenaApi = app(Calls::class);

        $ccdaExternal = $athenaApi->getCcd($enrollee->targetPatient->ehr_patient_id,
            $enrollee->targetPatient->ehr_practice_id,
            $enrollee->targetPatient->ehr_department_id);

        if ( ! isset($ccdaExternal[0])) {
            return [
                'patient' => $enrollee->nameAndDob(),
                'message' => 'Could not retrieve CCD from Athena',
                'type'    => 'error',
            ];
        }

        $ccda = Ccda::create([
            'practice_id' => $enrollee->practice_id,
            'vendor_id'   => 1,
            'xml'         => $ccdaExternal[0]['ccda'],
        ]);

        $enrollee->medical_record_id   = $ccda->id;
        $enrollee->medical_record_type = Ccda::class;
        $imported                      = $ccda->import();
        $enrollee->save();

        return [
            'patient' => $enrollee->nameAndDob(),
            'message' => "The CCD was imported. $url",
            'type'    => 'success',
        ];
    }

    private function eligibilityJob(Enrollee $enrollee)
    {
        if ($enrollee->eligibilityJob) {
            return $enrollee->eligibilityJob;
        }
        $hash = $enrollee->practice->name . $enrollee->first_name . $enrollee->last_name . $enrollee->mrn . $enrollee->city . $enrollee->state . $enrollee->zip;

        return EligibilityJob::whereHash($hash)->first();
    }

    private function importFromEligibilityJob(Enrollee $enrollee, EligibilityJob $job)
    {
        $service = app(ImportService::class);

        // Just another hack
        // To import CLH JSON format
        // @todo: Need to consolidate functionality from [Enrollees, EligibilityJobs, CCDAs, TabularMedicalRecords, _logs, _imports, phx tables]
        if ($job->batch->type == EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE) {
            $mr = new MedicalRecord($job, $enrollee->practice);

            $mr = Ccda::create([
                'practice_id'             => $enrollee->practice->id,
                'location_id'             => $enrollee->practice->primary_location_id,
                'mrn'                     => $job->data['patient_id'],
                'json'                    => $mr->toJson(),
                'referring_provider_name' => $job->data['preferred_provider'],
            ]);

            $imr = $mr->import();

            $enrollee->medical_record_id   = $mr->id;
            $enrollee->medical_record_type = Ccda::class;
            $enrollee->save();

            return $imr;
        }

        $imr = $service->createTabularMedicalRecordAndImport($job->data, $enrollee->practice);

        return $imr;
    }
}