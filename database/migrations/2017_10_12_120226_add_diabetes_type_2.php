<?php

use App\CarePlanTemplate;
use App\Models\CPM\CpmProblem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiabetesType2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $diabetes1 = CpmProblem::updateOrCreate([
            'name' => 'Diabetes Type 1',
            'default_icd_10_code' => 'E10.8',
        ]);

        $diabetes2 = CpmProblem::updateOrCreate([
            'name' => 'Diabetes Type 2',
            'default_icd_10_code' => 'E11.8',
        ]);

        $diabetes = CpmProblem::whereName('Diabetes')
            ->with('cpmInstructions')
            ->first();

        $defaultCarePlan = CarePlanTemplate::where('type', '=', 'CLH Default')->first();

        if (count($diabetes->cpmInstructions) > 0) {
            $defaultCarePlan->cpmProblems()->attach($diabetes1->id, [
                'cpm_instruction_id' => $diabetes->cpmInstructions[0]->id,
            ]);

            $defaultCarePlan->cpmProblems()->attach($diabetes2->id, [
                'cpm_instruction_id' => $diabetes->cpmInstructions[0]->id,
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
        //
    }
}
