<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOpsDashboardMysqlIndex2 extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lv_page_timer', function (Blueprint $table) {
            $table->dropIndex('index_for_nova_page_timer_query_2');
            $table->index([
                'start_time',
                'duration',
                'deleted_at',
                'id',
            ], 'index_for_nova_page_timer_query_2');
        });
    }
}
