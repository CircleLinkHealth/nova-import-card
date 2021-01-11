<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmMiscsUsersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_miscs_users');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cpm_miscs_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cpm_instruction_id')->unsigned()->nullable()->index('cpm_miscs_users_cpm_instruction_id_foreign');
            $table->integer('patient_id')->unsigned();
            $table->integer('cpm_misc_id')->unsigned()->index('cpm_miscs_users_cpm_misc_id_foreign');
            $table->timestamps();
            $table->index(['patient_id', 'cpm_misc_id']);
        });
    }
}
