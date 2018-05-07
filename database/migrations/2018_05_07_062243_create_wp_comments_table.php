<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWpCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('wp_comments', function(Blueprint $table)
		{
			$table->bigInteger('comment_ID', true)->unsigned();
			$table->bigInteger('comment_post_ID')->unsigned()->default(0)->index('comment_post_ID');
			$table->text('comment_author');
			$table->string('comment_author_email', 100)->index('comment_author_email');
			$table->string('comment_author_url', 200);
			$table->string('comment_author_IP', 100);
			$table->dateTime('comment_date')->default('0000-00-00 00:00:00');
			$table->dateTime('comment_date_gmt')->default('0000-00-00 00:00:00')->index('comment_date_gmt');
			$table->text('comment_content', 65535);
			$table->integer('comment_karma')->default(0);
			$table->string('comment_approved', 20)->default('1');
			$table->string('comment_agent');
			$table->string('comment_type', 20);
			$table->bigInteger('comment_parent')->unsigned()->default(0)->index('comment_parent');
			$table->bigInteger('user_id')->unsigned()->default(0);
			$table->index(['comment_approved','comment_date_gmt'], 'comment_approved_date_gmt');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('wp_comments');
	}

}
