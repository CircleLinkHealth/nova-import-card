<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmLifestylesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpm_lifestyles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('care_item_id')->unsigned()->nullable()->index('cpm_lifestyles_care_item_id_foreign');
            $table->string('name')->unique();
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
        Schema::drop('cpm_lifestyles');
    }

}
