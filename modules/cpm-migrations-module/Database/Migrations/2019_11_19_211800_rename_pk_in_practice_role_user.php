<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePkInPracticeRoleUser extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('practice_role_user', 'id')) {
            Schema::table('practice_role_user', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }

        if ( ! Schema::hasColumn('practice_role_user', 'key_id')) {
            Schema::table(
                'practice_role_user',
                function (Blueprint $table) {
                    $table->increments('key_id')->before('user_id');
                }
            );
        }
    }
}
