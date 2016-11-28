<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPatientCareTeamMembersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_care_team_members', function (Blueprint $table) {
            $table->foreign('member_user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_care_team_members', function (Blueprint $table) {
            $table->dropForeign('patient_care_team_members_member_user_id_foreign');
            $table->dropForeign('patient_care_team_members_user_id_foreign');
        });
    }

}
