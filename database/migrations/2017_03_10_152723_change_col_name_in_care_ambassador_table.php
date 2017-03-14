<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColNameInCareAmbassadorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {

            $table->dropColumn('month-year');
            $table->date('day')->after('care_ambassador_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {

            $table->date('month-year');
            $table->dropColumn('day');

        });
    }
}
