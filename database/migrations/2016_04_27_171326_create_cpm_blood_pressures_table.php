<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpmBloodPressuresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cpm_blood_pressures', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('starting');
			$table->string('target');
			$table->string('systolic_high_alert');
			$table->string('systolic_low_alert');
			$table->string('diastolic_high_alert');
			$table->string('diastolic_low_alert');
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
		Schema::drop('cpm_blood_pressures');
	}

}
