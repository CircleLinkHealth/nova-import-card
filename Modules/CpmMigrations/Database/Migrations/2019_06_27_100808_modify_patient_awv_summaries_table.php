<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPatientAwvSummariesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $dropColumnns = [];

        if (Schema::hasColumn('patient_awv_summaries', 'year')) {
            $dropColumnns[] = 'year';
        }

        if (Schema::hasColumn('patient_awv_summaries', 'is_initial_visit')) {
            $dropColumnns[] = 'is_initial_visit';
        }

        if ($dropColumnns) {
            foreach ($dropColumnns as $columnn) {
                Schema::table('patient_awv_summaries', function (Blueprint $table) use ($columnn) {
                    $table->dropColumn($columnn);
                });
            }
        }
        Schema::table('patient_awv_summaries', function (Blueprint $table) {
            $table->dateTime('initial_visit')->nullable();
            $table->dateTime('subsequent_visit')->nullable();
            $table->date('month_year');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        $dropColumnns = [];
        if (Schema::hasColumn('patient_awv_summaries', 'subsequent_visit')) {
            $dropColumnns[] = 'subsequent_visit';
        }

        if (Schema::hasColumn('patient_awv_summaries', 'initial_visit')) {
            $dropColumnns[] = 'initial_visit';
        }

        if (Schema::hasColumn('patient_awv_summaries', 'month_year')) {
            $dropColumnns[] = 'month_year';
        }

        if ($dropColumnns) {
            foreach ($dropColumnns as $columnn) {
                Schema::table('patient_awv_summaries', function (Blueprint $table) use ($columnn) {
                    $table->dropColumn($columnn);
                });
            }
        }

        Schema::table('patient_awv_summaries', function (Blueprint $table) {
            if ( ! Schema::hasColumn('patient_awv_summaries', 'year')) {
                $table->unsignedInteger('year')
                    ->after('user_id');
            }

            if ( ! Schema::hasColumn('patient_awv_summaries', 'is_initial_visit')) {
                $table->boolean('is_initial_visit')->default(0)->after('year');
            }
        });
    }
}
