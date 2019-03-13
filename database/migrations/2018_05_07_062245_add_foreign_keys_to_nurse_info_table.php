<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToNurseInfoTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('nurse_info', function (Blueprint $table) {
            $table->dropForeign('nurse_info_user_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('nurse_info', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
