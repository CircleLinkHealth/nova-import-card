<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\CareAmbassadorLog;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EraseTestEnrollees implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Erase test enrollees (created by seeder - having is_demo set as true in eligibilityJob->data)
     * And all potential related data that might be generated during the testing phase, including users created.
     * Also, reset CareAmbassador Logs for CA's that have called these patients.
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
            $time = PageTimer::where('enrollee_id', $enrollee->id)
                ->get();
            foreach ($time as $entry) {
                $entry->forceDelete();
            }

            //erase eligibility job
            $eligibilityJob = $enrollee->eligibilityJob;
            optional($eligibilityJob)->batch->delete();
            $eligibilityJob->delete();

            //erase user and data
            $user = $enrollee->user()->first();

            if ($user) {
                Ccda::where('patient_id', $user->id)->forceDelete();

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
