<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreIndexes2 extends Migration
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
        Schema::table('enrollees', function (Blueprint $table) {
            $table->index([
                'practice_id',
                'last_attempt_at',
            ]);
        });

        //for NovaPage Timer query
        Schema::table('lv_page_timer', function (Blueprint $table) {
            $table->dropIndex('lv_page_timer_provider_id_start_time_index');
            $table->index([
                'start_time',
                'duration',
                'deleted_at',
            ], 'index_for_nova_page_timer_query_2');
        });

        //for CCD Provider Search
        DB::statement('ALTER TABLE users ADD FULLTEXT full(display_name, first_name, last_name)');
    }
}
