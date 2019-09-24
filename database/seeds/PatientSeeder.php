<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Models\CPM\CpmProblem;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Seeder;
use Tests\Helpers\UserHelpers;

class PatientSeeder extends Seeder
{
    use UserHelpers;

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $problemIds = CpmProblem::get()
            ->pluck('id');
        $months      = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $practiceIds = Practice::activeBillable()->get()->pluck('id');

        factory(User::class, 2)->create([])->each(function ($u) use ($problemIds, $practiceIds, $months) {
            $practiceId = $practiceIds->random();
            $u->attachPractice($practiceId, [Role::whereName('participant')->value('id')]);
            $u->program_id = $practiceId;
            $u->save();

            $patientInfo = new \CircleLinkHealth\Customer\Entities\Patient();
            $patientInfo->user_id = $u->id;
            //patient info is saved
            $patientInfo->ccm_status = \CircleLinkHealth\Customer\Entities\Patient::ENROLLED;

            $u->patientSummaries()->create([
                'month_year' => Carbon::now()->copy()->subMonth($months->random())->startOfMonth()->toDateString(),
                'ccm_time'   => 1400,
                'approved'   => 1,
                'actor_id'   => 1,
            ]);

            $u->ccdProblems()->createMany([
                ['name' => 'test'.str_random(5)],
                ['name' => 'test'.str_random(5)],
            ]);
        });
    }
}
