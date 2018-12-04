<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmLifestylesUsersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_lifestyles_users');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cpm_lifestyles_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cpm_instruction_id')->unsigned()->nullable()->index('cpm_lifestyles_users_cpm_instruction_id_foreign');
            $table->integer('patient_id')->unsigned();
            $table->integer('cpm_lifestyle_id')->unsigned()->index('cpm_lifestyles_users_cpm_lifestyle_id_foreign');
            $table->timestamps();
            $table->index(['patient_id', 'cpm_lifestyle_id']);
        });
    }
}
