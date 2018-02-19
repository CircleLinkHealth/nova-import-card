<?php

use App\Models\CPM\CpmProblem;
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
        $problemIds = CpmProblem::get()
                                ->pluck('id');


        factory(User::class, 50)->create()->each(function ($u) use ($problemIds) {
            $u->attachPractice(8, null, null, 2);
            $u->program_id = 8;
            $u->save();
            $u->patientInfo()->create();
            $u->patientSummaries()->create([
                'month_year' => Carbon::now()->startOfMonth()->toDateString(),
                'ccm_time'   => 1400,
            ]);
            $u->chargeableServices()->attach(1);
            $u->cpmProblems()->attach($problemIds->random(5)->all());
        });



    }
}
