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

            $table->string('first_name');
            $table->string('last_name');
            $table->date('dob');
            $table->string('provider_name');

            $table->string('mrn');
            $table->string('gender');
            $table->string('language');

            $table->string('primary_phone');
            $table->string('cell_phone');
            $table->string('home_phone');
            $table->string('work_phone');

            $table->string('email');

            $table->string('address');
            $table->string('address2');
            $table->string('city');
            $table->string('state');
            $table->string('zip');

            $table->string('primary_insurance');
            $table->string('secondary_insurance');

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
