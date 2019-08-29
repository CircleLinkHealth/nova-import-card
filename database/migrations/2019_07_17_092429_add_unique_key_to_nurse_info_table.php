<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueKeyToNurseInfoTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('nurse_info', function (Blueprint $table) {
            $table->dropUnique('nurse_info_user_id_unique');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('nurse_info', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }
}
