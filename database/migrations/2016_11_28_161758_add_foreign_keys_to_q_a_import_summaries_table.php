<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToQAImportSummariesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('q_a_import_summaries', function (Blueprint $table) {
            $table->foreign('ccda_id')->references('id')->on('ccdas')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('q_a_import_summaries', function (Blueprint $table) {
            $table->dropForeign('q_a_import_summaries_ccda_id_foreign');
        });
    }

}
