<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRnApproverIdInCareplansTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('care_plans', function (Blueprint $table) {
            $table->dropForeign(['rn_approver_id']);
            $table->dropColumn('rn_approver_id');
            $table->dropColumn('rn_date');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_plans', function (Blueprint $table) {
            $table->dateTime('rn_date')
                ->nullable();

            $table->integer('rn_approver_id')
                ->unsigned()
                ->nullable();

            $table->foreign('rn_approver_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');
        });
    }
}
