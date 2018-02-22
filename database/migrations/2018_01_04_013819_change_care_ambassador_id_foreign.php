<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCareAmbassadorIdForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('enrollees', 'care_ambassador_user_id')) {
            return;
        }

        Schema::table('enrollees', function (Blueprint $table) {
            $table->dropForeign(['care_ambassador_user_id']);

            $table->renameColumn('care_ambassador_user_id', 'care_ambassador_id');

            $table->foreign('care_ambassador_id')
                  ->references('id')
                  ->on('care_ambassadors')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            //
        });
    }
}
