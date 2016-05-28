<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpmBloodSugarsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cpm_blood_sugars', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('starting');
			$table->string('target');
			$table->string('starting_a1c');
			$table->string('high_alert');
			$table->string('low_alert');
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
		Schema::drop('cpm_blood_sugars');
	}

}
