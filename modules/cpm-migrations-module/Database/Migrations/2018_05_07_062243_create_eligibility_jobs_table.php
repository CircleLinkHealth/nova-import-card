<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEligibilityJobsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('eligibility_jobs');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('eligibility_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('batch_id')->unsigned()->index('eligibility_jobs_batch_id_foreign');
            $table->string('hash', 100)->nullable()->index();
            $table->integer('status')->nullable()->index();
            $table->text('data');
            $table->string('outcome', 20)->nullable()->index();
            $table->text('messages')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
