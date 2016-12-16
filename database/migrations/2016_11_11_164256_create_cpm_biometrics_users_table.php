<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmBiometricsUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpm_biometrics_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cpm_instruction_id')->unsigned()->nullable()->index('cpm_biometrics_users_cpm_instruction_id_foreign');
            $table->integer('cpm_biometric_id')->unsigned();
            $table->integer('patient_id')->unsigned()->index('cpm_biometrics_users_patient_id_foreign');
            $table->timestamps();
            $table->unique([
                'cpm_biometric_id',
                'patient_id',
            ]);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cpm_biometrics_users');
    }

}
