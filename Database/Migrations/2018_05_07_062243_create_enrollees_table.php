<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEnrolleesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('enrollees');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('enrollees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('batch_id')->unsigned()->nullable()->index('enrollees_batch_id_foreign');
            $table->string('medical_record_type')->nullable();
            $table->integer('medical_record_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable()->index('enrollees_user_id_foreign');
            $table->integer('provider_id')->unsigned()->nullable()->index('enrollees_provider_id_foreign');
            $table->integer('practice_id')->unsigned()->nullable();
            $table->integer('care_ambassador_id')->unsigned()->nullable()->index('enrollees_care_ambassador_id_foreign');
            $table->integer('total_time_spent')->default(0);
            $table->text('last_call_outcome', 65535)->nullable();
            $table->text('last_call_outcome_reason', 65535)->nullable();
            $table->string('mrn', 100);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address');
            $table->string('address_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('primary_phone');
            $table->string('other_phone');
            $table->string('home_phone');
            $table->string('cell_phone');
            $table->date('dob')->nullable();
            $table->string('lang')->default('EN');
            $table->text('invite_code', 65535);
            $table->string('status');
            $table->integer('attempt_count')->unsigned();
            $table->string('preferred_days')->nullable();
            $table->string('preferred_window')->nullable();
            $table->date('invite_sent_at')->nullable();
            $table->dateTime('consented_at')->nullable();
            $table->dateTime('last_attempt_at')->nullable();
            $table->dateTime('invite_opened_at')->nullable();
            $table->timestamps();
            $table->string('primary_insurance');
            $table->string('secondary_insurance');
            $table->string('tertiary_insurance');
            $table->boolean('has_copay')->default(0);
            $table->string('email');
            $table->date('last_encounter')->nullable();
            $table->string('referring_provider_name')->nullable();
            $table->boolean('confident_provider_guess')->nullable();
            $table->longText('problems')->nullable();
            $table->integer('cpm_problem_1')->unsigned()->nullable()->index('enrollees_cpm_problem_1_foreign');
            $table->integer('cpm_problem_2')->unsigned()->nullable()->index('enrollees_cpm_problem_2_foreign');
            $table->unique(['practice_id', 'mrn']);
            $table->unique(['practice_id', 'first_name', 'last_name', 'dob']);
        });
    }
}
