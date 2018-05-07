<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWpWfBadLeechersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('wp_wfBadLeechers', function(Blueprint $table)
		{
			$table->integer('eMin')->unsigned();
			$table->binary('IP', 16)->default('                ');
			$table->integer('hits')->unsigned();
			$table->primary(['eMin','IP']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('wp_wfBadLeechers');
	}

}
