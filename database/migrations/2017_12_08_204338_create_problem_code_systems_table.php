<?php

use App\Constants;
use App\ProblemCodeSystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemCodeSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problem_code_systems', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        ProblemCodeSystem::create(['name' => Constants::ICD9_NAME]);
        ProblemCodeSystem::create(['name' => Constants::ICD10_NAME]);
        ProblemCodeSystem::create(['name' => Constants::SNOMED_NAME]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('problem_code_systems');
    }
}
