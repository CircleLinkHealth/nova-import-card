<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeSomeEnrolleesFieldsNullable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->string('lang')->nullable()->change();
            $table->string('primary_insurance')->nullable()->change();
            $table->string('secondary_insurance')->nullable()->change();
            $table->string('tertiary_insurance')->nullable()->change();
        });
    }
}
