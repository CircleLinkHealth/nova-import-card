<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCalendarFieldsToNurseContactTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('nurse_contact_window', function (Blueprint $table) {
            $table->dropColumn('repeat_frequency');
            $table->date('repeat_start');
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
            $table->date('repeat_start')->nullable();
            $table->date('until')->nullable();
            $table->enum('validated', ['not_checked', 'worked', 'not_worked'])->default('not_checked');
        });
    }
}
