<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeEnrolleesCareAmbassadorIdToCareAmbassadorUserId extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(
            'enrollees',
            function (Blueprint $table) {
                $table->renameColumn('care_ambassador_user_id', 'care_ambassador_id');
                $table->dropForeign('enrollees_care_ambassador_user_id_foreign');

                $table->foreign('care_ambassador_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
        );
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table(
            'enrollees',
            function (Blueprint $table) {
                $table->renameColumn('care_ambassador_id', 'care_ambassador_user_id');

                if ( ! isOnSqlite()) {
                    $table->dropForeign('enrollees_care_ambassador_id_foreign');
                }

                $table->foreign('care_ambassador_user_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
        );
    }
}
