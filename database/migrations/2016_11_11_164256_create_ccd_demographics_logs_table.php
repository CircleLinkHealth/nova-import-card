<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdDemographicsLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccd_demographics_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ccda_id')->unsigned()->index('ccd_demographics_logs_ccda_id_foreign');
            $table->integer('vendor_id')->unsigned()->index('ccd_demographics_logs_vendor_id_foreign');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('mrn_number')->nullable();
            $table->string('street')->nullable();
            $table->string('street2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip', 5)->nullable();
            $table->string('cell_phone', 12)->nullable();
            $table->string('home_phone', 12)->nullable();
            $table->string('work_phone', 12)->nullable();
            $table->string('email')->nullable();
            $table->string('language')->nullable();
            $table->string('race')->nullable();
            $table->string('ethnicity')->nullable();
            $table->boolean('import');
            $table->boolean('invalid');
            $table->boolean('edited');
            $table->softDeletes();
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
        Schema::drop('ccd_demographics_logs');
    }

}
