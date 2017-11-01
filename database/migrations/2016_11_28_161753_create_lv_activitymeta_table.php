<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvActivitymetaTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lv_activitymeta', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('activity_id')->unsigned()->index('activity_id');
            $table->integer('comment_id')->unsigned();
            $table->string('message_id', 30);
            $table->string('meta_key')->nullable()->index('meta_key');
            $table->text('meta_value');
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
        Schema::drop('lv_activitymeta');
    }
}
