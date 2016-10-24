<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class SaveRedirects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lv_page_timer', function (Blueprint $table) {
            if (!Schema::hasColumn('lv_page_timer', 'redirect_to')) {
                $table->string('redirect_to')
                    ->nullable()
                    ->after('actual_end_time');
            }
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
            $table->dropColumn('redirect_to');
        });
    }
}
