<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientCareTeamMembersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_care_team_members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index('patient_care_team_members_user_id_foreign');
            $table->integer('member_user_id')->unsigned()->index('patient_care_team_members_member_user_id_foreign');
            $table->string('type');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('patient_care_team_members');
    }

}
