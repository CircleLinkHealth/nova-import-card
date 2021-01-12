<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDatabaseFields extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('survey_instances', function (Blueprint $table) {
            if ( ! Schema::hasColumn('survey_instances', 'start_date')) {
                $table->dateTime('start_date')->nullable();
            }
            if ( ! Schema::hasColumn('survey_instances', 'end_date')) {
                $table->dateTime('end_date')->nullable();
            }
        });

        Schema::table('users_surveys', function (Blueprint $table) {
            if (Schema::hasColumn('users_surveys', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('users_surveys', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('survey_instances', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('survey_instances', 'start_date')) {
                $columns[] = 'start_date';
            }
            if (Schema::hasColumn('survey_instances', 'end_date')) {
                $columns[] = 'end_date';
            }

            $table->dropColumn($columns);
        });

        Schema::table('users_surveys', function (Blueprint $table) {
            if ( ! Schema::hasColumn('users_surveys', 'start_date')) {
                $table->dateTime('start_date')->nullable()->after('status');
            }
            if ( ! Schema::hasColumn('users_surveys', 'completed_at')) {
                $table->dateTime('completed_at')->nullable()->after('start_date');
            }
        });
    }
}
