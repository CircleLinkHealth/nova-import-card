<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserScopeToUser extends Migration
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
        if (Schema::hasColumn('users', 'scope')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            $table->enum('scope', [
                Practice::SCOPE_CARE_TEAM,
                Practice::SCOPE_LOCATION,
                Practice::SCOPE_PRACTICE,
            ])->nullable()->after('program_id');
        });
    }
}
