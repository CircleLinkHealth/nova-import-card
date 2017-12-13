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
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('ehr_patient_id');
            $table->unsignedInteger('ehr_practice_id');
            $table->unsignedInteger('ehr_department_id');
            $table->enum('status', ['to_process', 'eligible', 'ineligible', 'consented', 'enrolled']);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('ehr_id')
                ->references('id')->on('ehrs')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
