<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;


use App\CareAmbassadorLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

class EraseTestEnrollees implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     *
     * Erase test enrollees (created by seeder - having is_demo set as true in eligibilityJob->data)
     * And all potential related data that might be generated during the testing phase, including users created.
     * Also, reset CareAmbassador Logs for CA's that have called these patients.
     *
     *
     * @throws \Exception
     */
    public function handle()
    {
        $enrollees = Enrollee::whereHas('eligibilityJob', function ($j) {
            //only check for this. These are only seeder enrollees.
            $j->where('data->is_demo', 'true');
        })
                             ->get();

        foreach ($enrollees as $enrollee) {
            //erase eligibility job
            $enrollee->eligibilityJob()->delete();

            //erase ccda data
            $imr = $enrollee->getImportedMedicalRecord();
            if ($imr) {
                $ccda = $imr->medicalRecord();
                if ($ccda) {
                    $ccda->forceDelete();
                }
                $imr->forceDelete();
            }

            //erase user and data
            $user = $enrollee->user()->first();

            if ($user) {
                $user->patientSummaries()->delete();
                $user->forceDelete();
            }

            $careAmbassador = $enrollee->careAmbassador()->first();

            if ($careAmbassador) {
                $date = $enrollee->updated_at->format('Y-m-d');
                CareAmbassadorLog::where('enroller_id', $careAmbassador->careAmbassador->id)
                                 ->where('day', $date)
                                 ->delete();
            }

            $enrollee->delete();
        }
    }
}
