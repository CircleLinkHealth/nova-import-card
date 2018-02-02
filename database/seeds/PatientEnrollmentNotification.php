<?php

use App\User;
use App\CarePlan;
use App\CareplanAssessment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PatientEnrollmentNotification extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admins = User::ofType('administrator')->get();
        CarePlan::where('provider_date', '>=', Carbon::yesterday())->with('assessment')->get()->map(function ($c) {
            if ($c->assessment) {
                // $admins->map(function ($user) use ($c) {
                //     $user->notify(new SendAssessmentNotification($c->assessment));
                // });
                $practice = $c->assessment->approver()->first()->practices()->first();
                if ($practice) {
                    $location = $practice->primaryLocation()->first();
                    if ($location) {
                        $location->notify(new SendAssessmentNotification($c->assessment));
                    }
                }
            }
        });
    }
}
