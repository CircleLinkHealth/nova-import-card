<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCallWindowToCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calls', function (Blueprint $table) {

            $table->text('window_start');
            $table->text('window_end');
            $table->date('call_date');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calls', function (Blueprint $table) {

            $table->dropColumn('window_start');
            $table->dropColumn('window_end');
            $table->dropColumn('call_date');

        });
    }
}
