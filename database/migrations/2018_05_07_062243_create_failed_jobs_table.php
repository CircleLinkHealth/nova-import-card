<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFailedJobsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('failed_jobs');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->text('connection', 65535);
            $table->text('queue', 65535);
            $table->text('payload');
            $table->text('exception');
            $table->timestamp('failed_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }
}
