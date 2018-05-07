<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWpBlogVersionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('wp_blog_versions', function(Blueprint $table)
		{
			$table->bigInteger('blog_id')->default(0)->primary();
			$table->string('db_version', 20)->index('db_version');
			$table->dateTime('last_updated')->default('0000-00-00 00:00:00');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('wp_blog_versions');
	}

}
