<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInsuranceLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insurance_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('medical_record_type')->nullable();
            $table->integer('medical_record_id')->unsigned()->nullable();
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('policy_id')->nullable()->default('');
            $table->string('relation')->nullable()->default('');
            $table->string('subscriber')->nullable()->default('');
            $table->boolean('import');
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
        Schema::drop('insurance_logs');
    }
}
