<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEhrKeychainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ehr_keychains', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('ehr_patient_id');
            $table->unsignedInteger('ehr_id');
            $table->unsignedInteger('ehr_practice_id');
            $table->unsignedInteger('ehr_department_id');
            $table->timestamps();

            $table->unique(['patient_id', 'ehr_id']);

            $table->foreign('patient_id')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('ehr_id')
                  ->references('id')->on('ehrs')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ehr_keychains');
    }
}
