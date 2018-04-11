<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToForeignIdsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('foreign_ids', function (Blueprint $table) {
            $table->foreign(
                'location_id',
                'location_foreign'
            )->references('id')->on('locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(
                'user_id',
                'user_id_foreign'
            )->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('foreign_ids', function (Blueprint $table) {
            $table->dropForeign('location_foreign');
            $table->dropForeign('user_id_foreign');
        });
    }
}
