<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddsBillableDuration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lv_page_timer', function (Blueprint $table) {
            $table->unsignedInteger('billable_duration')
                ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lv_page_timer', function (Blueprint $table) {
            $table->dropColumn('billable_duration');
        });
    }
}
