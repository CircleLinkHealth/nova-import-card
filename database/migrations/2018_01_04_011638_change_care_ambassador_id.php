<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCareAmbassadorId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->dropForeign(['care_ambassador_id']);

            $table->renameColumn('care_ambassador_id', 'care_ambassador_user_id');

            $table->foreign('care_ambassador_user_id')
                ->references('id')
                ->on('users')
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
