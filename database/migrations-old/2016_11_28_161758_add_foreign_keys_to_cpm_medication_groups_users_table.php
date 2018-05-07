<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCpmMedicationGroupsUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_medication_groups_users', function (Blueprint $table) {
            $table->foreign(
                'cpm_medication_group_id',
                'cpm_med_groups_users_rel_foreign'
            )->references('id')->on('cpm_medication_groups')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cpm_instruction_id')->references('id')->on('cpm_instructions')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('patient_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cpm_medication_groups_users', function (Blueprint $table) {
            $table->dropForeign('cpm_med_groups_users_rel_foreign');
            $table->dropForeign('cpm_medication_groups_users_cpm_instruction_id_foreign');
            $table->dropForeign('cpm_medication_groups_users_patient_id_foreign');
        });
    }
}
