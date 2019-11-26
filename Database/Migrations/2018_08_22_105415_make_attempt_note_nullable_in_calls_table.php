<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeAttemptNoteNullableInCallsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->string('attempt_note')->nullable(false)->change();
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->string('attempt_note')->nullable()->change();
        });
    }
}
