<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChargeableServicesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('chargeable_services');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('chargeable_services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->text('description', 65535)->nullable();
            $table->decimal('amount')->nullable();
            $table->timestamps();
        });
    }
}
