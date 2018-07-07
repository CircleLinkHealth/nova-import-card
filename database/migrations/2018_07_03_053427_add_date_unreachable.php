<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateUnreachable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->dateTime('date_unreachable')
                  ->after('date_withdrawn')
                  ->nullable();

            $table->dateTime('date_withdrawn')
                  ->nullable()
                  ->change();

            $table->dateTime('date_paused')
                  ->nullable()
                  ->change();
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
            $table->dropColumn('date_unreachable');
        });
    }
}
