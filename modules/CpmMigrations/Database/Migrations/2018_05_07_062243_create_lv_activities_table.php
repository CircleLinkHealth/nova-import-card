<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLvActivitiesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('lv_activities');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'lv_activities',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('type')->nullable()->index('type');
                $table->integer('duration')->unsigned();
                $table->string('duration_unit', 30)->nullable();
                $table->integer('patient_id')->unsigned()->index('patient_id');
                $table->integer('provider_id')->unsigned()->index('provider_id');
                $table->integer('logger_id')->unsigned()->nullable();
                $table->integer('comment_id')->unsigned()->index('comment_id')->nullable();
                $table->integer('sequence_id')->unsigned()->nullable();
                $table->string('obs_message_id', 30)->index('obs_message_id')->nullable();
                $table->string('logged_from', 30)->nullable();
                $table->dateTime('performed_at')->nullable()->index('preformed_at');
                $table->dateTime('performed_at_gmt')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->integer('page_timer_id')->unsigned()->nullable();
                $table->index(
                    ['patient_id', 'logged_from', 'provider_id', 'performed_at', 'type'],
                    'pat_lgFrm_prov_perfAt_type'
                );
            }
        );
    }
}
