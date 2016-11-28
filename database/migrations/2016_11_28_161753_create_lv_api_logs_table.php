<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvApiLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lv_api_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('api_key_id')->unsigned()->nullable()->index('api_logs_api_key_id_foreign');
            $table->string('route', 150)->index('api_logs_route_index');
            $table->string('method', 6)->index('api_logs_method_index');
            $table->text('params', 65535);
            $table->string('ip_address');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('lv_api_logs');
    }

}
