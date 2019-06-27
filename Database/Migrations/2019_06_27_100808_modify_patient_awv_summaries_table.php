<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyPatientAwvSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_awv_summaries', function (Blueprint $table) {
            if (Schema::hasColumn('patient_awv_summaries', 'subsequent_visit')) {
                $table->dropColumn('subsequent_visit');

            }

            if (Schema::hasColumn('patient_awv_summaries', 'initial_visit')) {
                $table->dropColumn('subsequent_visit');

            }

            if (Schema::hasColumn('patient_awv_summaries', 'month_year')) {
                $table->dropColumn('month_year');

            }

            $table->unsignedInteger('year')
                  ->after('patient_id');

            if (! Schema::hasColumn('patient_awv_summaries', 'is_initial_visit')) {
                $table->boolean('is_initial_visit')->default(0)->after('year');

            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_awv_summaries', function (Blueprint $table) {
            if (Schema::hasColumn('patient_awv_summaries', 'year')) {
                $table->dropColumn('year');

            }

            if (Schema::hasColumn('patient_awv_summaries', 'is_initial_visit')) {
                $table->dropColumn('is_initial_visit');

            }

            $table->dateTime('initial_visit')->nullable();
            $table->dateTime('subsequent_visit')->nullable();
            $table->date('month_year');
        });
    }
}
