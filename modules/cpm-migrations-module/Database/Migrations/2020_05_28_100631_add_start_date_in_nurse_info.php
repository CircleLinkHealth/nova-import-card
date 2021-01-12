<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartDateInNurseInfo extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('nurse_info', 'start_date')) {
            Schema::table('nurse_info', function (Blueprint $table) {
                $table->dropColumn('start_date');
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
        if ( ! Schema::hasColumn('nurse_info', 'start_date')) {
            Schema::table('nurse_info', function (Blueprint $table) {
                $table->date('start_date')
                    ->after('user_id')
                    ->default(null)
                    ->nullable(true);
            });
        }
    }
}
