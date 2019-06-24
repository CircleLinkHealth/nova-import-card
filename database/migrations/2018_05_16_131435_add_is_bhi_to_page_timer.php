<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsBhiToPageTimer extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('lv_page_timer', function (Blueprint $table) {
            $table->dropColumn('is_behavioral');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if ( ! Schema::hasColumn('lv_page_timer', 'is_behavioral')) {
            Schema::table('lv_page_timer', function (Blueprint $table) {
                $table->boolean('is_behavioral')->after('provider_id')->nullable();
            });
        }
    }
}
