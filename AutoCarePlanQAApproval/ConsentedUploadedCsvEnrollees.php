<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\AutoCarePlanQAApproval;

use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConsentedUploadedCsvEnrollees implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @param \CircleLinkHealth\Eligibility\ProcessEligibilityService $importService
     */
    public function handle()
    {
        $this->consentedEnrollees()
            ->orderByDesc('consented_at')
            ->each(function (Enrollee $enrollee) {
                $this->attachMostRecentCcd($enrollee);

                if ($enrollee->isDirty()) {
                    $enrollee->save();
                    ImportAndApproveEnrollee::dispatch($enrollee->id);
                }
            });
    }

    private function attachMostRecentCcd(Enrollee &$enrollee): Enrollee
    {
        $ccdaId = Ccda::where('source', Ccda::EMR_DIRECT)
            ->where('practice_id', $enrollee->practice_id)
            ->where('patient_dob', $enrollee->dob->toDateString())
            ->where('patient_mrn', $enrollee->mrn)
            ->orderByDesc('id')
            ->value('id');

        if ($ccdaId) {
            $enrollee->medical_record_id   = $ccdaId;
            $enrollee->medical_record_type = Ccda::class;
        }

        return $enrollee;
    }

    private function consentedEnrollees()
    {
        return Enrollee::where('status', '=', Enrollee::CONSENTED)
            ->where('source', '=', Enrollee::UPLOADED_CSV)
            ->whereHas('practice', function ($q) {
                $q->activeBillable()->whereIsDemo(0);
            });
    }
}
