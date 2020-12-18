<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeMessageIdNullable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lv_activitymeta', function (Blueprint $table) {
            $table->integer('message_id')->nullable(false)->change();
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lv_activitymeta', function (Blueprint $table) {
            $table->integer('message_id')->nullable(true)->default(null)->change();
        });
    }
}
