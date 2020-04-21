<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnrolleeIdInLvPageTimerTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('lv_page_timer', 'enrollee_id')) {
            Schema::table('lv_page_timer', function (Blueprint $table) {
                $table->dropForeign(['enrollee_id']);
                $table->dropColumn('enrollee_id');
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
        //for L5.8
        config(['database.connections.mysql.strict' => false]);
        DB::reconnect();
        DB::statement('ALTER TABLE lv_page_timer CHANGE start_time start_time TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE lv_page_timer CHANGE end_time end_time TIMESTAMP NULL DEFAULT NULL');
        //for L5.8

        Schema::table('lv_page_timer', function (Blueprint $table) {
            $table->unsignedInteger('enrollee_id')
                ->after('patient_id')
                ->nullable()
                ->default(null);

            $table->foreign('enrollee_id')
                ->references('id')
                ->on('enrollees')
                ->onUpdate('no action')
                ->onDelete('no action');
        });
    }
}
