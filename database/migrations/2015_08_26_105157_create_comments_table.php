<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('comment_post_ID');
			$table->string('comment_author');
			$table->string('comment_author_email');
			$table->string('comment_author_url');
			$table->string('comment_author_IP');
			$table->timestamp('comment_date');
			$table->timestamp('comment_date_gmt');
			$table->mediumText('comment_content');
			$table->unsignedInteger('comment_karma');
			$table->string('comment_approved', 20);
			$table->string('comment_agent');
			$table->string('comment_type', 20);
			$table->unsignedInteger('comment_parent');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('program_id');
			$table->unsignedInteger('legacy_comment_id');
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
		Schema::drop('lv_observations');
	}

}
