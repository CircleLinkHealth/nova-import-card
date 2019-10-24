<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumsToNurseContactWindow extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('nurse_contact_window', function (Blueprint $table) {
            $table->dropColumn('repeat_frequency');
            $table->dropColumn('manually_saved');
            $table->dropColumn('exclude_events_dates');
            $table->dropColumn('until');
            $table->dropColumn('validated');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('nurse_contact_window', function (Blueprint $table) {
            $table->enum('repeat_frequency', ['daily', 'monthly', 'weekly', 'does_not_repeat'])->nullable();
            $table->boolean('manually_edited')->default(false);
            $table->json('exclude_events_dates')->nullable();
            $table->date('until')->nullable();
            $table->enum('validated', ['not_checked', 'worked', 'not_worked'])->default('not_checked');
            $table->dateTime('hide_from_calendar')->nullable();
        });
    }
}
