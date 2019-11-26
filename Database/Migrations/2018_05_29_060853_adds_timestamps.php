<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsTimestamps extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('practice_role_user', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if ( ! Schema::hasColumn('practice_role_user', 'created_at')) {
            Schema::table('practice_role_user', function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }
}
