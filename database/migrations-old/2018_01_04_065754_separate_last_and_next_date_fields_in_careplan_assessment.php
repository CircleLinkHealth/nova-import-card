<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeparateLastAndNextDateFieldsInCareplanAssessment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('careplan_assessments', function (Blueprint $table) {
            $table->dropColumn('diabetes_screening_last_and_next_date');
            $table->dropColumn('eye_screening_last_and_next_date');

            $table->date('diabetes_screening_last_date');
            $table->date('diabetes_screening_next_date');
            $table->date('eye_screening_last_date');
            $table->date('eye_screening_next_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('careplan_assessments', function (Blueprint $table) {
            $table->dropColumn('diabetes_screening_last_date');
            $table->dropColumn('diabetes_screening_next_date');
            $table->dropColumn('eye_screening_last_date');
            $table->dropColumn('eye_screening_next_date');

            $table->string('diabetes_screening_last_and_next_date', 4294960);
            $table->string('eye_screening_last_and_next_date', 4294960);
        });
    }
}
