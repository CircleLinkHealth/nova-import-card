<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAddendumsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addendums', function (Blueprint $table) {
            $table->increments('id');
            $table->string('addendumable_type');
            $table->integer('addendumable_id')->unsigned();
            $table->integer('author_user_id')->unsigned()->index('addendums_author_user_id_foreign');
            $table->text('body');
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
        Schema::drop('addendums');
    }
}
