<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWpSiteTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('wp_site', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('domain', 200);
			$table->string('path', 100);
			$table->index(['domain','path'], 'domain');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('wp_site');
	}

}
