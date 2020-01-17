<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCallProblemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_problems', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('call_id');
            $table->unsignedInteger('ccd_problem_id');
            $table->unsignedInteger('patient_monthly_summary_id')->nullable();

            $table->foreign('call_id')
                ->references('id')
                ->on('calls')
                ->onDelete('cascade');

            $table->foreign('ccd_problem_id')
                  ->references('id')
                  ->on('ccd_problems');

            $table->foreign('patient_monthly_summary_id')
                  ->references('id')
                  ->on('patient_monthly_summaries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('call_problems');
    }
}
