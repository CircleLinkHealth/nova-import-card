<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorPractices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->dropColumn('short_display_name');
            $table->dropColumn('site_id');
            $table->dropColumn('path');
            $table->dropColumn('registered');
            $table->dropColumn('last_updated');
            $table->dropColumn('public');
            $table->dropColumn('archived');
            $table->dropColumn('mature');
            $table->dropColumn('spam');
            $table->dropColumn('deleted');
            $table->dropColumn('lang_id');
            $table->dropColumn('att_config');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('practices', function (Blueprint $table) {
            //
        });
    }
}
