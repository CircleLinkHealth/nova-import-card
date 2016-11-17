<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCpmMiscsUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_miscs_users', function (Blueprint $table) {
            $table->foreign('cpm_instruction_id')->references('id')->on('cpm_instructions')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('cpm_misc_id')->references('id')->on('cpm_miscs')->onUpdate('CASCADE')->onDelete('CASCADE');
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
        Schema::table('cpm_miscs_users', function (Blueprint $table) {
            $table->dropForeign('cpm_miscs_users_cpm_instruction_id_foreign');
            $table->dropForeign('cpm_miscs_users_cpm_misc_id_foreign');
            $table->dropForeign('cpm_miscs_users_patient_id_foreign');
        });
    }

}
