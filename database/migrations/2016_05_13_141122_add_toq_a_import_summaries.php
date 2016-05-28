<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToqAImportSummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('q_a_import_summaries', function (Blueprint $table) {
            $table->dropColumn('duplicate_ids');
            $table->unsignedInteger('duplicate_id')->after('flag')->nullable();
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
            $table->string('duplicate_ids');
            $table->dropColumn('duplicate_id');
        });
    }
}
