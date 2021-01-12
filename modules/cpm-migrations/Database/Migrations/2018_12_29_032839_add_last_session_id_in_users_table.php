<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastSessionIdInUsersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'last_session_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('last_session_id');
            });
        }
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if ( ! Schema::hasColumn('users', 'last_session_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('last_session_id')->nullable();
            });
        }
    }
}
