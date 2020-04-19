<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\ImportService;
use CircleLinkHealth\Eligibility\ValueObjects\BlueButtonMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;
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
                                if ($enrollee->user_id && User::whereId($enrollee->user_id)->exists()) {
                                    $this->enrolleeAlreadyImported($enrollee);

                                    return null;
                                }

                                //verify it wasn't already imported
                                $imr = $enrollee->getImportedMedicalRecord();
                                if ($imr) {
                                    if ($imr->patient_id) {
                                        $enrollee->user_id = $imr->patient_id;
                                        $enrollee->save();

                                        $this->enrolleeAlreadyImported($enrollee);

                                        return null;
                                    }

                                    $this->enrolleeMedicalRecordImported($enrollee);

                                    return null;
                                }

                                //import ccda
                                if ($importService->isCcda($enrollee->medical_record_type)) {
                                    return $importService->importExistingCcda($enrollee->medical_record_id);
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

        return ['importconsentedenrollees', 'enrollees:' . $ids];
    }

    private function eligibilityJob(Enrollee $enrollee)
    {
        if ($enrollee->eligibilityJob) {
            return $enrollee->eligibilityJob;
        }
        $hash = $enrollee->practice->name . $enrollee->first_name . $enrollee->last_name . $enrollee->mrn . $enrollee->city . $enrollee->state . $enrollee->zip;

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
            $mr = new BlueButtonMedicalRecord($job, $enrollee->practice);

            $provider = $job->data['preferred_provider'];

            $exists = Ccda::where('referring_provider_name', $provider)
                          ->where('practice_id', $enrollee->practice->id)
                          ->whereNotNull('billing_provider_id')
                          ->whereNotNull('location_id')
                          ->first();

            $mr = Ccda::create(
                [
                    'practice_id'             => $enrollee->practice->id,
                    'location_id'             => optional(
                                                     $exists
                                                 )->location_id ?? $enrollee->practice->primary_location_id,
                    'billing_provider_id'     => optional($exists)->billing_provider_id ?? null,
                    'mrn'                     => $job->data['patient_id'],
                    'json'                    => $mr->toJson(),
                    'referring_provider_name' => $provider,
                ]
            );

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
        \Log::channel('logdna')->warning($message);

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
}

