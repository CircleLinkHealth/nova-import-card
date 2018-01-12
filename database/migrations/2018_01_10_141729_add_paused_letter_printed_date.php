<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPausedLetterPrintedDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->dateTime('paused_letter_printed_at')
                ->nullable()
                ->after('ccm_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->dropColumn('paused_letter_printed_at');
        });
    }
}
