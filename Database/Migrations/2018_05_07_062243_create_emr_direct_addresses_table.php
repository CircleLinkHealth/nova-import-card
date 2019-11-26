<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmrDirectAddressesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('emr_direct_addresses');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('emr_direct_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('emrDirectable_type');
            $table->integer('emrDirectable_id')->unsigned();
            $table->string('address');
            $table->timestamps();
        });
    }
}
