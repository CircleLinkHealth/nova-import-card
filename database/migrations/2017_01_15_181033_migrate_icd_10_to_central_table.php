<?php

use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Models\CPM\CpmProblem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateIcd10ToCentralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $cpmProblems = CpmProblem::all();

        foreach ($cpmProblems as $cpmProblem) {
            $maps = SnomedToCpmIcdMap::whereBetween('icd_10_code', [
                $cpmProblem->icd10from,
                $cpmProblem->icd10to,
            ])->update([
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
        Schema::table('snomed_to_cpm_icd_maps', function (Blueprint $table) {
            //
        });
    }
}
