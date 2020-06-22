<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Seeder;

class OpsDashboardDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $ccmStatuses = collect([
            Patient::UNREACHABLE,
            Patient::PAUSED,
            Patient::WITHDRAWN,
            Patient::WITHDRAWN_1ST_CALL,
        ]);
        $date = Carbon::now()->startOfMonth();

        $timeDuration = collect([150, 275, 348, 567, 764, 895, 988, 1010, 1111, 1235, 1300]);

        $patients = User::with(['patientInfo'])
            ->whereHas('patientInfo', function ($p) {
                $p->enrolled();
            })
            ->get();

        $patientsToLose = $patients->random(40);
        foreach ($patientsToLose as $p) {
            try {
                $p->patientInfo->ccm_status = $ccmStatuses->random();
                $p->save();
            } catch (\Exception $e) {
                //do nothing
            }
        }

        foreach ($patients as $patient) {
            $summary = $patient->patientSummaries()->firstOrCreate(
                [
                    'month_year' => $date->toDateString(),
                ]
            );
            if ($summary) {
                $summary->ccm_time = $timeDuration->random();
                $summary->bhi_time = $timeDuration->random();
                $summary->save();
            }
        }
    }
}
