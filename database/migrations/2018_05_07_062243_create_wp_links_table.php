<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWpLinksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('wp_links', function(Blueprint $table)
		{
			$table->bigInteger('link_id', true)->unsigned();
			$table->string('link_url');
			$table->string('link_name');
			$table->string('link_image');
			$table->string('link_target', 25);
			$table->string('link_description');
			$table->string('link_visible', 20)->default('Y')->index('link_visible');
			$table->bigInteger('link_owner')->unsigned()->default(1);
			$table->integer('link_rating')->default(0);
			$table->dateTime('link_updated')->default('0000-00-00 00:00:00');
			$table->string('link_rel');
			$table->text('link_notes', 16777215);
			$table->string('link_rss');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('wp_links');
	}

}
