<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameFamilyMemberColumnFromEnrolleeFamilyMembersTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollee_family_members', function (Blueprint $table) {
            $table->renameColumn('family_member_enrollee_id', 'family_member_id');

            $table->dropUnique('enrollee_family_unique');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollee_family_members', function (Blueprint $table) {
            $table->renameColumn('family_member_id', 'family_member_enrollee_id');
            $table->unique(['enrollee_id', 'family_member_enrollee_id'], 'enrollee_family_unique');
        });
    }
}
