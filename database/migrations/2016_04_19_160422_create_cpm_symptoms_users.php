<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpmSymptomsUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cpm_symptoms_users', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('user_id');
			$table->foreign('user_id')
				->references('id')
				->on('wp_users')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->unsignedInteger('cpm_symptoms_id');
			$table->foreign('cpm_symptoms_id')
				->references('id')
				->on('cpm_symptoms')
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
		Schema::drop('cpm_symptoms_users');
	}

}
