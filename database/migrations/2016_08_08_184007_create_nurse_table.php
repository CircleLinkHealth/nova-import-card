<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNurseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('nurse_info')) {

            Schema::create('nurse_info', function (Blueprint $table) {

                $table->increments('id');

                $table->unsignedInteger('user_id');
                $table->text('status');
                $table->text('license');
                $table->integer('hourly_rate');
                $table->boolean('spanish');

                $table->foreign('user_id')
                    ->references('ID')
                    ->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->timestamps();

            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('nurse_info');
    }
}