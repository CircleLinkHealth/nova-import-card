<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeAndSubtypeInCallsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('sub_type');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->string('type')
                ->nullable()
                ->after('id');
            $table->string('sub_type')
                ->nullable()
                ->before('note_id');
        });
    }
}
