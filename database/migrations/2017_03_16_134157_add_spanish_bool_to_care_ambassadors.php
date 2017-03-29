<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpanishBoolToCareAmbassadors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_ambassadors', function (Blueprint $table) {

            $table->boolean('speaks_spanish')->after('hourly_rate')->defualt(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('care_ambassadors', function (Blueprint $table) {

            $table->dropColumn('speaks_spanish');

        });
    }
}
