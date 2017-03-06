<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsvMedicalRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tabular_medical_records', function (Blueprint $table) {
            $table->increments('id');

            //practice_id
            $table->unsignedInteger('practice_id')
                ->nullable();

            $table->foreign('practice_id')
                ->references('id')
                ->on('practices')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            //location_id
            $table->unsignedInteger('location_id')
                ->nullable();

            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            //billing_provider_id
            $table->unsignedInteger('billing_provider_id')
                ->nullable();

            $table->foreign('billing_provider_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            //uploaded_by
            $table->unsignedInteger('uploaded_by')
                ->nullable();

            $table->foreign('uploaded_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            //patient_id
            $table->unsignedInteger('patient_id')
                ->nullable();

            $table->foreign('patient_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('dob')->nullable();
            $table->string('provider_name')->nullable();

            $table->string('mrn')->nullable();
            $table->string('gender')->nullable();
            $table->string('language')->nullable();

            $table->string('primary_phone')->nullable();
            $table->string('cell_phone')->nullable();
            $table->string('home_phone')->nullable();
            $table->string('work_phone')->nullable();

            $table->string('email')->nullable();

            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();

            $table->string('primary_insurance')->nullable();
            $table->string('secondary_insurance')->nullable();

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
        Schema::dropIfExists('tabular_medical_records');
    }
}
