<?php

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

        ProblemCodeSystem::create(['name' => 'ICD 9']);
        ProblemCodeSystem::create(['name' => 'ICD 10']);
        ProblemCodeSystem::create(['name' => 'SNOMED']);
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
