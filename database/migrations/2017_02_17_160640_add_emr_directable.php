<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmrDirectable extends Migration
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
            $table->unsignedInteger('emrDirectable_id');

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
        Schema::dropIfExists('emr_direct_addresses');
    }
}
