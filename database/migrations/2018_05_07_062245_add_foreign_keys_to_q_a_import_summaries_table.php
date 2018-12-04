<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToQAImportSummariesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('q_a_import_summaries', function (Blueprint $table) {
            $table->dropForeign('q_a_import_summaries_ccda_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('q_a_import_summaries', function (Blueprint $table) {
            $table->foreign('ccda_id')->references('id')->on('ccdas')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
