<?php

use App\Models\CPM\CpmProblem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDrugUseDisorderToDefaultCarePlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $carePlanTemplate = getDefaultCarePlanTemplate();

        $cpmProblem = CpmProblem::whereName('Drug Use Disorder')->first();

        $carePlanTemplate->cpmProblems()->attach($cpmProblem->id);
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
