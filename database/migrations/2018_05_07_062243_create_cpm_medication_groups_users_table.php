<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmMedicationGroupsUsersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_medication_groups_users');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cpm_medication_groups_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cpm_instruction_id')->unsigned()->nullable()->index('cpm_medication_groups_users_cpm_instruction_id_foreign');
            $table->integer('patient_id')->unsigned();
            $table->integer('cpm_medication_group_id')->unsigned()->index('cpm_med_groups_users_rel_foreign');
            $table->timestamps();
            $table->index(['patient_id', 'cpm_medication_group_id'], 'cpm_med_grps_usrs_ptnt_id_cpm_med_grp_id_index');
        });
    }
}
