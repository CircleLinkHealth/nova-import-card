<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientAWVSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_awv_summaries', function (Blueprint $table) {
            $table->increments('id');
            $table->date('month_year');
            $table->dateTime('initial_visit')->nullable();
            $table->dateTime('subsequent_visit')->nullable();
            $table->boolean('is_billable')->default(0);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_awv_summaries');
    }
}
