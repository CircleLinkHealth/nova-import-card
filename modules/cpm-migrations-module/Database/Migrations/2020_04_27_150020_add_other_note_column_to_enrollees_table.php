<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherNoteColumnToEnrolleesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('enrollees', 'other_note')) {
            Schema::table('enrollees', function (Blueprint $table) {
                $table->dropColumn('other_note');
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
        if ( ! Schema::hasColumn('enrollees', 'other_note')) {
            Schema::table('enrollees', function (Blueprint $table) {
                $table->string('other_note')->nullable()->after('last_call_outcome_reason');
            });
        }
    }
}
