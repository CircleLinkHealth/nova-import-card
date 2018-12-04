<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToNurseContactWindowTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('nurse_contact_window', function (Blueprint $table) {
            $table->dropForeign('nurse_contact_window_nurse_info_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('nurse_contact_window', function (Blueprint $table) {
            $table->foreign('nurse_info_id')->references('id')->on('nurse_info')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
