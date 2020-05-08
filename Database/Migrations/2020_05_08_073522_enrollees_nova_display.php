<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class EnrolleesNovaDisplay extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enrollees_nova_display', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('enrollee_id');
            $table->unsignedInteger('user_id_from_enrollee')->nullable();
            $table->string('awv_survey_status')->nullable();
            $table->boolean('logged_in')->default(false);
            $table->timestamps();
        });
    }
}
