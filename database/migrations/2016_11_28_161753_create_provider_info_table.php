<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProviderInfoTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index('provider_info_user_id_foreign');
            $table->string('prefix')->nullable();
            $table->string('qualification')->nullable();
            $table->string('npi_number')->nullable();
            $table->string('specialty')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('provider_info');
    }
}
