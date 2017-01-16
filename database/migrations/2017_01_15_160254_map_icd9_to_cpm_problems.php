<?php

use App\Models\CPM\CpmProblem;
use App\SnomedToICD9Map;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MapIcd9ToCpmProblems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Seed table first
        Artisan::call('db:seed', array('--class' => 'SnomedToIcd9MapTableSeeder'));

        $cpmProblems = CpmProblem::all();

        foreach ($cpmProblems as $cpmProblem) {
            SnomedToICD9Map::whereBetween('code', [
                $cpmProblem->icd9from,
                $cpmProblem->icd9to,
            ])->update([
                'ccm_eligible' => true,
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
