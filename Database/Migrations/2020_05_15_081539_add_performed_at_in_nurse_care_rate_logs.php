<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformedAtInNurseCareRateLogs extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('nurse_care_rate_logs', 'performed_at')) {
            Schema::table('nurse_care_rate_logs', function (Blueprint $table) {
                $table->dropColumn('performed_at');
            });
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('nurse_care_rate_logs', 'performed_at')) {
            Schema::table('nurse_care_rate_logs', function (Blueprint $table) {
                $table->timestamp('performed_at')->nullable();
            });
        }
    }
}
