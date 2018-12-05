<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToNurseMonthlySummariesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('nurse_monthly_summaries', function (Blueprint $table) {
            $table->dropForeign('nurse_monthly_summaries_nurse_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('nurse_monthly_summaries', function (Blueprint $table) {
            $table->foreign('nurse_id')->references('id')->on('nurse_info')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
