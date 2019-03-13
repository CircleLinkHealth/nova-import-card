<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCpmMiscsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cpm_miscs', function (Blueprint $table) {
            $table->dropForeign('cpm_miscs_care_item_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cpm_miscs', function (Blueprint $table) {
            $table->foreign('care_item_id')->references('id')->on('care_items')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }
}
