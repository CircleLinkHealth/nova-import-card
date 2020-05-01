<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class MakeCommentNullableInAppointmentsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->text('comment')->nullable(false)->change();
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->text('comment')->nullable(true)->change();
        });
    }
}
