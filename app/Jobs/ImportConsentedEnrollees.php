<?php

namespace App\Jobs;

use App\EligibilityBatch;
use App\Enrollee;
use App\Services\CCD\ProcessEligibilityService;
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
     * @param ProcessEligibilityService $processEligibilityService
     *
     * @return void
     */
    public function handle(ProcessEligibilityService $processEligibilityService)
    {
        $imported = Enrollee::whereIn('id', $this->enrolleeIds)
                            ->get()
                            ->map(function ($enrollee) use ($processEligibilityService) {
                                $url = route('import.ccd.remix',
                                    'Click here to Create and a CarePlan and review.');
                                
                                if ($enrollee->user_id) {
                                    return [
                                        'patient' => $enrollee->nameAndDob(),
                                        'message' => 'This patient has already been imported',
                                        'type'    => 'error',
                                    ];
                                }

                                $imr = $enrollee->getImportedMedicalRecord();

                                if ($imr) {
                                    if ($imr->patient_id) {
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

                                if ($processEligibilityService->isCcda($enrollee->medical_record_type)) {
                                    $response = $processEligibilityService->importExistingCcda($enrollee->medical_record_id);

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
    }
}
