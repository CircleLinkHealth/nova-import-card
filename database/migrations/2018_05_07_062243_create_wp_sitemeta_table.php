<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWpSitemetaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('wp_sitemeta', function(Blueprint $table)
		{
			$table->bigInteger('meta_id', true);
			$table->bigInteger('site_id')->default(0)->index('site_id');
			$table->string('meta_key')->nullable()->index('meta_key');
			$table->text('meta_value')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('wp_sitemeta');
	}

}
