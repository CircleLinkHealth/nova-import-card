<?php

use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use Illuminate\Database\Migrations\Migration;

class MigrateDiabetesType1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $plainDiabetes = CpmProblem::whereName('Diabetes')->first();

        //
        // Type 1
        //
        //

        $diabetes1 = CpmProblem::whereName('Diabetes Type 1')->first();

        $type1Problems = Problem::where('name', 'like', '%diabetes%')
            ->where(function ($q) {
                $q->where('name', 'like', '%type I %')
                    ->orWhere('name', 'like', '%type I')
                    ->orWhere('name', 'like', '%type I,')
                    ->orWhere('name', 'like', '%type 1 %')
                    ->orWhere('name', 'like', '%type 1%');
            })
            ->with('patient');

        $type1Problems->update([
            'cpm_problem_id' => $diabetes1->id,
        ]);

        $type1Patients = $type1Problems->pluck('patient_id')->unique()->all();

        DB::table('cpm_problems_users')
            ->where('cpm_problem_id', $plainDiabetes->id)
            ->whereIn('patient_id', $type1Patients)
            ->update([
                'cpm_problem_id' => $diabetes1->id,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
