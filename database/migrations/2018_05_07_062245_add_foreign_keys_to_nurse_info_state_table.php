<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToNurseInfoStateTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nurse_info_state', function (Blueprint $table) {
            $table->foreign('nurse_info_id')->references('id')->on('nurse_info')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('state_id', 'nurse_info_state_states_id_foreign')->references('id')->on('states')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nurse_info_state', function (Blueprint $table) {
            $table->dropForeign('nurse_info_state_nurse_info_id_foreign');
            $table->dropForeign('nurse_info_state_states_id_foreign');
        });
    }
}
