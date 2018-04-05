<?php

use App\Models\CPM\CpmProblem;
use App\Practice;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Tests\Helpers\UserHelpers;

class PatientMonthlySummariesSeeder extends Seeder
{
    use UserHelpers;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $problemIds  = CpmProblem::get()
                                 ->pluck('id');
        $months      = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $practiceIds = Practice::activeBillable()->get()->pluck('id');


        factory(User::class, 50)->create()->each(function ($u) use ($problemIds, $practiceIds, $months) {
            $practiceId = $practiceIds->random();
            $u->attachPractice($practiceId, null, null, 2);
            $u->program_id = $practiceId;
            $u->save();
            $u->patientInfo()->create();
            $u->patientSummaries()->create([
                'month_year' => Carbon::now()->copy()->subMonth($months->random())->startOfMonth()->toDateString(),
                'ccm_time'   => 1400,
                'approved'   => 1,
                'actor_id'   => 1,
            ]);
            $u->chargeableServices()->attach(1);
            $u->cpmProblems()->attach($problemIds->random(5)->all());
        });


    }
}
