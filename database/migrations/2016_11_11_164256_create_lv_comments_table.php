<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvCommentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lv_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('comment_post_ID')->unsigned();
            $table->string('comment_author');
            $table->string('comment_author_email');
            $table->string('comment_author_url');
            $table->string('comment_author_IP');
            $table->dateTime('comment_date')->default('0000-00-00 00:00:00');
            $table->dateTime('comment_date_gmt')->default('0000-00-00 00:00:00');
            $table->text('comment_content', 16777215);
            $table->integer('comment_karma')->unsigned();
            $table->string('comment_approved', 20);
            $table->string('comment_agent');
            $table->string('comment_type', 20);
            $table->integer('comment_parent')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('program_id')->unsigned();
            $table->integer('legacy_comment_id')->unsigned();
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
        Schema::drop('lv_comments');
    }

}
