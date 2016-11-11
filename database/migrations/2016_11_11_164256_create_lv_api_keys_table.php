<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvApiKeysTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lv_api_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('key', 40)->unique('api_keys_key_unique');
            $table->smallInteger('level');
            $table->boolean('ignore_limits');
            $table->timestamps();
            $table->softDeletes();
            $table->string('client_name');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('lv_api_keys');
    }

}
