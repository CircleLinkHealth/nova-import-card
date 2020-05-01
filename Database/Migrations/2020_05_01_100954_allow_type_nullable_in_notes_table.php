<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class AllowTypeNullableInNotesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notes', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->text('type')->nullable(false)->change();
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notes', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->text('type')->nullable(true)->change();
        });
    }
}
