<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoEnrollmentFieldInEnrollees extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            if (Schema::hasColumn('enrollees', 'auto_enrollment_triggered')) {
                $table->dropColumn('auto_enrollment_triggered');
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
        if ( ! Schema::hasColumn('enrollees', 'auto_enrollment_triggered')) {
            Schema::table('enrollees', function (Blueprint $table) {
                $table->boolean('auto_enrollment_triggered')->default(false);
            });
        }
    }
}
