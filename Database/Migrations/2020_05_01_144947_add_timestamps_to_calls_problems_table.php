<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToCallsProblemsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumns('call_problems', ['created_at', 'updated_at'])) {
            Schema::table('call_problems', function (Blueprint $table) {
                $table->dropTimestamps();
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
        if ( ! Schema::hasColumns('call_problems', ['created_at', 'updated_at'])) {
            Schema::table('call_problems', function (Blueprint $table) {
                $table->timestamps(0);
            });
        }
    }
}
