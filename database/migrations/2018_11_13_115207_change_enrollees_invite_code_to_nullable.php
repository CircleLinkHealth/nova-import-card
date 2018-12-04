<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ChangeEnrolleesInviteCodeToNullable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('enrollees', function ($table) {
            $table->text('invite_code')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
            $table->integer('attempt_count')->nullable(false)->change();
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('enrollees', function ($table) {
            $table->text('invite_code')->nullable(true)->change();
            $table->string('status')->nullable(true)->change();
            $table->integer('attempt_count')->nullable(true)->change();
        });
    }
}
