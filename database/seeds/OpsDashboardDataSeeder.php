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

        $nurses = User::ofType('care-center')->where('access_disabled', 0)->pluck('id');
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
            ->whereHas('patientInfo', function ($p){
                $p->enrolled();
            })
            ->get();

        foreach($patients as $patient){
            if ($patient->primaryPractice){
                $patient->activities()->createMany([
                    [
                        'type' => $activityType->random(),
                        'duration' => $activityDuration->random(),
                        'duration_unit' => 'seconds',
                        'performed_at' => $date->copy()->subDay(5)->toDateTimeString(),
                        'provider_id' => $nurses->random(),
                        ],
                    [
                        'type' => $activityType->random(),
                        'duration' => $activityDuration->random(),
                        'duration_unit' => 'seconds',
                        'performed_at' => $date->copy()->subDay(5)->toDateTimeString(),
                        'provider_id' => $nurses->random(),
                    ]]);
            }else{
                $patient->attachPractice($practiceIds->random(), null, null, 2);
                $patient->activities()->createMany([
                    [
                        'type' => $activityType->random(),
                        'duration' => $activityDuration->random(),
                        'duration_unit' => 'seconds',
                        'performed_at' => $date->copy()->subDay(5)->toDateTimeString(),
                        'provider_id' => $nurses->random(),],
                    [
                        'type' => $activityType->random(),
                        'duration' => $activityDuration->random(),
                        'duration_unit' => 'seconds',
                        'performed_at' => $date->copy()->subDay(5)->toDateTimeString(),
                        'provider_id' => $nurses->random(),
                    ]]);
            }

        }


//        factory(User::class, 50)->create()->each(function ($u) use ($practiceIds, $date, $activityDuration, $activityType) {
//            $practiceId = $practiceIds->random();
//            $u->attachPractice($practiceId, null, null);
//            $u->program_id = $practiceId;
//            $u->save();
////            $u->patientInfo()->create();
////            $u->patientInfo->ccm_status = 'enrolled';
////            $u->patientInfo->registration_date = $date->startOfMonth()->toDateTimeString();
////            $u->patientInfo->save();
//            Patient::updateOrCreate([
//                'user_id' => $u->id,
//                'ccm_status' => 'enrolled',
//                'registration_date' => $date->subDay(5)->toDateTimeString(),
//            ]);
//            $u->activities()->createMany(
//                [
//                    'type' => $activityType->random(),
//                    'duration' => $activityDuration->random(),
//                    'duration_unit' => 'seconds',
//                    'performed_at' => $date->subDay(rand(1,20))->toDateTimeString()],
//                [
//                    'type' => $activityType->random(),
//                    'duration' => $activityDuration->random(),
//                    'duration_unit' => 'seconds',
//                    'performed_at' => $date->subDay(rand(1,20))->toDateTimeString()
//                ]
//            );
//        });
    }
}
