<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFaxLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fax_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('from');
            $table->string('to');
            $table->string('type');
            $table->boolean('delivered');
            $table->string('message');
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
        Schema::drop('fax_logs');
    }
}
