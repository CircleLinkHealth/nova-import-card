<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreInfoToNurses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('nurse_info', function (Blueprint $table) {

            $table->boolean('isNLC')->default(false);

        });

        Schema::create('nurse_info_state', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('nurse_info_id');
            $table->unsignedInteger('states_id');

            $table->foreign('nurse_info_id')
                ->references('id')
                ->on('nurse_info')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('states_id')
                ->references('id')
                ->on('states')
                ->onDelete('cascade')
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
        Schema::table('nurse_info', function (Blueprint $table) {

            $table->dropColumn('isNLC');

        });

        Schema::table('nurse_info_state', function (Blueprint $table) {

            $table->drop();

        });

    }
}
