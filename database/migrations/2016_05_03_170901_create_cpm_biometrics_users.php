<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpmBiometricsUsers extends Migration
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

            $table->unsignedInteger('cpm_biometric_id');
            $table->foreign('cpm_biometric_id')
                ->references('id')
                ->on((new \App\Models\CPM\CpmBiometric())->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedInteger('patient_id');
            $table->foreign('patient_id')
                ->references('id')
                ->on((new \App\User())->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
        Schema::drop('cpm_biometrics_users');
    }
}
