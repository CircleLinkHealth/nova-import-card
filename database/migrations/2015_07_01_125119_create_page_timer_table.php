<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageTimerTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('page_timer', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('duration');
			$table->string('duration_unit', 30)->nullable();
			$table->unsignedInteger('patient_id');
			$table->unsignedInteger('provider_id');
			$table->timestamp('start_time');
			$table->timestamp('start_time_gmt');
			$table->timestamp('end_time');
			$table->timestamp('end_time_gmt');
			$table->string('url_full', 200)->nullable();
			$table->string('url_short', 200)->nullable();
			$table->unsignedInteger('program_id');
			$table->string('ip_addr', 200)->nullable();
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
		Schema::drop('page_timer');
	}

}
