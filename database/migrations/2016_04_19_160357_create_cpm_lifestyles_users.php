<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpmLifestylesUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cpm_lifestyles_users', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('user_id');
			$table->foreign('user_id')
				->references('id')
				->on('wp_users')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->unsignedInteger('cpm_lifestyles_id');
			$table->foreign('cpm_lifestyles_id')
				->references('id')
				->on('cpm_lifestyles')
				->onUpdate('cascade')
				->onDelete('cascade');

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
		Schema::drop('cpm_lifestyles_users');
	}

}
