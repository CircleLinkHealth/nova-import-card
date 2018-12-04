<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePracticeEnrollmentTipsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('practice_enrollment_tips');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('practice_enrollment_tips', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('practice_id');
            $table->longText('content');
            $table->timestamps();

            $table->foreign('practice_id')
                ->references('id')
                ->on('practices')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }
}
