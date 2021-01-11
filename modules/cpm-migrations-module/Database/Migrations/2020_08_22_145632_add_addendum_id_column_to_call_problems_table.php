<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddendumIdColumnToCallProblemsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_problems', function (Blueprint $table) {
            $table->dropForeign(['addendum_id']);

            $table->dropColumn('addendum_id');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_problems', function (Blueprint $table) {
            $table->unsignedInteger('addendum_id')->nullable()->after('call_id');

            $table->foreign('addendum_id')
                ->references('id')
                ->on('addendums')
                ->onDelete('set null');
        });
    }
}
