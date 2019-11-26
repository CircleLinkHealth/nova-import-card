<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLvPermissionsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('lv_permissions');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'lv_permissions',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('display_name')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
            }
        );
    }
}
