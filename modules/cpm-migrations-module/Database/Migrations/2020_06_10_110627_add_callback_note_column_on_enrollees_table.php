<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCallbackNoteColumnOnEnrolleesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('enrollees', 'callback_note')) {
            Schema::table('enrollees', function (Blueprint $table) {
                $table->dropColumn('callback_note');
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
        if ( ! Schema::hasColumn('enrollees', 'callback_note')) {
            Schema::table('enrollees', function (Blueprint $table) {
                $table->string('callback_note')->nullable()->after('requested_callback');
            });
        }
    }
}
