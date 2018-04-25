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
                                if ($enrollee->user_id) {
                                    return [
                                        'patient' => $enrollee->name(),
                                        'message' => 'This patient has already been imported',
                                        'type'    => 'error',
                                    ];
                                }

                                if ($processEligibilityService->isCcda($enrollee->medical_record_type)) {
                                    $response = $processEligibilityService->importExistingCcda($enrollee->medical_record_id);

                                    if ($response->imr) {
                                        $url = route('import.ccd.remix',
                                            'Click here to Create and a CarePlan and review.');

                                        return [
                                            'patient' => $enrollee->name(),
                                            'message' => "The CCD was imported. $url",
                                            'type'    => 'success',
                                        ];
                                    }
                                }

                                return [
                                    'patient' => $enrollee->name(),
                                    'message' => 'Sorry. Some random error occured. Please post to #qualityassurance to notify everyone to stop using the importer, and also tag Michalis to fix this asap.',
                                    'type'    => 'error',
                                ];
                            });

        if ($this->batch) {
            \Cache::put("batch:{$this->batch->id}:last_consented_enrollee_import", $imported->toJson(), 14400);
        }
    }
}
