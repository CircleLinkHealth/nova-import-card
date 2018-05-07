<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCallsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->foreign('inbound_cpm_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('note_id')->references('id')->on('notes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('outbound_cpm_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropForeign('calls_inbound_cpm_id_foreign');
            $table->dropForeign('calls_note_id_foreign');
            $table->dropForeign('calls_outbound_cpm_id_foreign');
        });
    }
}
