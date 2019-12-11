<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Enrollee;
use App\Models\MedicalRecords\Ccda;
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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var EligibilityBatch
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
     * @param ProcessEligibilityService $importService
     */
    public function handle(ImportService $importService)
    {
        ini_set('max_execution_time', 300);

        $imported = collect();

        Enrollee::whereIn('id', $this->enrolleeIds)
            ->with(['targetPatient', 'practice', 'eligibilityJob'])
            ->chunk(10, function ($enrollees) use ($importService, &$imported) {
                $newImported = $enrollees->map(function ($enrollee) use ($importService) {
                    //verify it wasn't already imported
                    if ($enrollee->user_id) {
                        $this->enrolleeAlreadyImported($enrollee);

                        return;
                    }

                    //verify it wasn't already imported
                    $imr = $enrollee->getImportedMedicalRecord();
                    if ($imr) {
                        if ($imr->patient_id) {
                            $enrollee->user_id = $imr->patient_id;
                            $enrollee->save();

                            $this->enrolleeAlreadyImported($enrollee);

                            return;
                        }

                        $this->enrolleeMedicalRecordImported($enrollee);

                        return;
                    }

                    //import PHX
                    if (139 == $enrollee->practice_id) {
                        ImportPHXEnrollee::dispatch($enrollee);

                        return $enrollee;
                    }

                    //import from AthenaAPI
                    if ($enrollee->targetPatient) {
                        return $this->importTargetPatient($enrollee);
                    }

                    //import ccda
                    if ($importService->isCcda($enrollee->medical_record_type)) {
                        $response = $importService->importExistingCcda($enrollee->medical_record_id);

                        if ($response->imr) {
                            $this->enrolleeAlreadyImported($enrollee);

                            return $response->imr;
                        }
                    }

                    //import from eligibility jobs
                    $job = $this->eligibilityJob($enrollee);
                    if ($job) {
                        return $this->importFromEligibilityJob($enrollee, $job);
                    }

                    throw new \Exception("This should never be reached. enrollee:$enrollee->id");
                });

                $imported = $imported->merge($newImported);
            });

        if ($this->batch && $imported->isNotEmpty()) {
            \Log::info($imported->toJson());

            \Cache::put("batch:{$this->batch->id}:last_consented_enrollee_import", $imported->toJson(), 14400);
        }

        return $imported;
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
        $service = app(ImportService::class);

        // Just another hack
        // To import CLH JSON format
        // @todo: Need to consolidate functionality from [Enrollees, EligibilityJobs, CCDAs, TabularMedicalRecords, _logs, _imports, phx tables]
        if (EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE == $job->batch->type) {
            $mr = new MedicalRecord($job, $enrollee->practice);

            $provider = $job->data['preferred_provider'];

            $exists = Ccda::where('referring_provider_name', $provider)
                ->where('practice_id', $enrollee->practice->id)
                ->whereNotNull('billing_provider_id')
                ->whereNotNull('location_id')
                ->first();

            $mr = Ccda::create([
                'practice_id'             => $enrollee->practice->id,
                'location_id'             => optional($exists)->location_id ?? $enrollee->practice->primary_location_id,
                'billing_provider_id'     => optional($exists)->billing_provider_id ?? null,
                'mrn'                     => $job->data['patient_id'],
                'json'                    => $mr->toJson(),
                'referring_provider_name' => $provider,
            ]);

            $imr = $mr->import();

            $enrollee->medical_record_id   = $mr->id;
            $enrollee->medical_record_type = Ccda::class;
            $enrollee->save();

            return $imr;
        }

        return $service->createTabularMedicalRecordAndImport($job->data, $enrollee->practice);
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

        $ccda = Ccda::create([
            'practice_id' => $enrollee->practice_id,
            'vendor_id'   => 1,
            'xml'         => $ccdaExternal[0]['ccda'],
        ]);

        $enrollee->medical_record_id   = $ccda->id;
        $enrollee->medical_record_type = Ccda::class;
        $imported                      = $ccda->import();
        $enrollee->save();

        $this->enrolleeMedicalRecordImported($enrollee);
    }

    private function log($message)
    {
        \Log::channel('logdna')->warning($message);

        sendSlackMessage('#parse_enroll_import', $message);
    }
}
