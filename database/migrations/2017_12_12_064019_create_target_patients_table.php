<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('target_patients', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ehr_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('ehr_patient_id');
            $table->unsignedInteger('practice_id');
            $table->unsignedInteger('department_id');
            $table->enum('status', []);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('target_patients');
    }
}
