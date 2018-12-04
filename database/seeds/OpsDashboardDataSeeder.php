<?php

use App\Patient;
use Illuminate\Database\Seeder;
use App\Models\CPM\CpmProblem;
use App\User;
use App\Practice;
use Carbon\Carbon;
use Tests\Helpers\UserHelpers;

class OpsDashboardDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ccmStatuses = collect([
            Patient::UNREACHABLE,
            Patient::PAUSED,
            Patient::WITHDRAWN
        ]);
        $nurses = User::ofType('care-center')->pluck('id');
        $practiceIds = Practice::active()->get()->pluck('id');
        $date = Carbon::now();
        $activityDuration = collect([150, 275, 348, 567, 764, 895, 988, 1010, 1111, 1235, 1300]);
        $activityType = collect([
            'CarePlanSetup',
            'ReviewProgress',
            'CareCoordination',
            'MedicationReconciliation',
            'Alerts Review',
        ]);

        $patients = User::with('patientInfo')
            ->whereHas('patientInfo', function ($p) {
                $p->enrolled();
            })
            ->get();

        $patientsToLose = $patients->random(40);
        foreach ($patientsToLose as $p) {
            $p->patientInfo->ccm_status = $ccmStatuses->random();
            $p->save();
        }

        foreach ($patients as $patient) {
            if ($patient->primaryPractice) {
                $patient->activities()->createMany([
                    [
                        'type' => $activityType->random(),
                        'duration' => $activityDuration->random(),
                        'duration_unit' => 'seconds',
                        'performed_at' => $date->copy()->subDay(1)->toDateTimeString(),
                        'provider_id' => $nurses->random(),
                        ],
                    ]);
            } else {
                $patient->attachPractice($practiceIds->random(), null, null, 2);
                $patient->activities()->createMany([
                    [
                        'type' => $activityType->random(),
                        'duration' => $activityDuration->random(),
                        'duration_unit' => 'seconds',
                        'performed_at' => $date->copy()->subDay(5)->toDateTimeString(),
                        'provider_id' => $nurses->random(),],
                ]);
            }
        }
    }
}
