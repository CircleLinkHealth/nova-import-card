<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProviderInfoTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('provider_info');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('provider_info', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_clinical')->nullable();
            $table->integer('user_id')->unsigned()->index('provider_info_user_id_foreign');
            $table->string('prefix')->nullable();
            $table->string('npi_number')->nullable();
            $table->string('specialty')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
