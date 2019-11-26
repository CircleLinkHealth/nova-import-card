<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTableHealthChecks extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('health_checks');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('health_checks', function (Blueprint $table) {
            $table->increments('id');

            $table->string('resource_name');

            $table->string('resource_slug')->index();

            $table->string('target_name');

            $table->string('target_slug')->index();

            $table->string('target_display');

            $table->boolean('healthy');

            $table->text('error_message')->nullable();

            $table->float('runtime');

            $table->string('value')->nullable();

            $table->string('value_human')->nullable();

            $table->timestamp('created_at', 0)->index();
        });
    }
}
