<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpsDashboardPracticeReports extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ops_dashboard_practice_reports');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasTable('ops_dashboard_practice_reports')) {
            Schema::create('ops_dashboard_practice_reports', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('practice_id');
                $table->date('date');
                $table->json('data')->nullable();
                $table->boolean('is_processed')->default(0);

                $table->timestamps();

                $table->foreign('practice_id')
                    ->references('id')
                    ->on('practices')
                    ->onDelete('cascade');
            });
        }
    }
}
