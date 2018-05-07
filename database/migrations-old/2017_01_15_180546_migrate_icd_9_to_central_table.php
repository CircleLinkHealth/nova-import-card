<?php

use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\SnomedToICD9Map;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateIcd9ToCentralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (SnomedToICD9Map::all() as $problem) {
            SnomedToCpmIcdMap::where('snomed_code', '=', $problem->snomed_code)
                ->update([
                    'icd_9_code'      => $problem->code,
                    'icd_9_name'      => $problem->name,
                    'icd_9_avg_usage' => $problem->avg_usage,
                    'icd_9_is_nec'    => $problem->is_nec,
                    'cpm_problem_id'  => $problem->cpm_problem_id,
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
