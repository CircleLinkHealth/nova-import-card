<?php

use App\Models\CPM\CpmProblem;
use App\SnomedToICD9Map;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCpmIdToIcd9 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('snomed_to_icd9_map', function (Blueprint $table) {
            $table->unsignedInteger('cpm_problem_id')
                ->nullable();

            $table->foreign('cpm_problem_id')
                ->references('id')
                ->on('cpm_problems')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        $cpmProblems = CpmProblem::all();

        foreach ($cpmProblems as $cpmProblem) {
            SnomedToICD9Map::whereBetween('code', [
                $cpmProblem->icd9from,
                $cpmProblem->icd9to,
            ])->update([
                'ccm_eligible'   => true,
                'cpm_problem_id' => $cpmProblem->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('snomed_to_icd9_map', function (Blueprint $table) {
            //
        });
    }
}
