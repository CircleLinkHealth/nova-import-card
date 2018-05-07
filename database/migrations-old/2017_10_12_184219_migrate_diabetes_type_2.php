<?php

use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use Illuminate\Database\Migrations\Migration;

class MigrateDiabetesType2 extends Migration
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
        // Type 2
        //
        //

        $diabetes2 = CpmProblem::whereName('Diabetes Type 2')->first();

        $type2Problems = Problem::where('name', 'like', '%diabetes%')
            ->where(function ($q) {
                $q->where('name', 'like', '%type 2%')
                    ->orWhere('name', 'like', '%type II%');
            })
            ->with('patient');

        $type2Problems->update([
            'cpm_problem_id' => $diabetes2->id,
        ]);

        $type2Patients = $type2Problems->pluck('patient_id')->unique()->all();

        DB::table('cpm_problems_users')
            ->where('cpm_problem_id', $plainDiabetes->id)
            ->whereIn('patient_id', $type2Patients)
            ->update([
                'cpm_problem_id' => $diabetes2->id,
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
