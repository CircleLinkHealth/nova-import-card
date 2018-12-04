<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientCareTeamMembersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('patient_care_team_members');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('patient_care_team_members', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('alert')->default(1);
            $table->integer('user_id')->unsigned()->index('patient_care_team_members_user_id_foreign');
            $table->integer('member_user_id')->unsigned()->index('patient_care_team_members_member_user_id_foreign');
            $table->string('type')->index();
            $table->timestamps();
        });
    }
}
