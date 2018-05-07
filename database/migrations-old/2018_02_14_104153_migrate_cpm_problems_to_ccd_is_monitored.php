<?php

use App\Models\CCD\Problem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateCpmProblemsToCcdIsMonitored extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('cpm_problems_users')->orderBy('id')->chunk(1000, function ($cpmProblemUsers) {
            foreach ($cpmProblemUsers as $cpmProblemUser) {
                $exists = App\Models\CCD\Problem::whereCpmProblemId($cpmProblemUser->id)
                    ->wherePatientId($cpmProblemUser->patient_id)
                    ->first();

                if ($exists) {
                    $exists->cpm_problem_id = $cpmProblemUser->cpm_problem_id;
                    $exists->is_monitored       = true;
                    $exists->cpm_instruction_id = $cpmProblemUser->cpm_instruction_id;

                    $exists->save();

                    continue;
                }


                App\Models\CCD\Problem::updateOrCreate([
                    'cpm_problem_id' => $cpmProblemUser->cpm_problem_id,
                    'patient_id'     => $cpmProblemUser->patient_id,
                ], [
                    'is_monitored'       => true,
                    'cpm_instruction_id' => $cpmProblemUser->cpm_instruction_id,
                ]);
            }
        });
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
