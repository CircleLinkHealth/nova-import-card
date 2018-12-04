<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmrDirectAddressesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
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


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('emr_direct_addresses');
    }
}
