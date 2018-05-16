<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTzTimezonesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tz_timezones', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('zone_id')->unsigned();
			$table->string('abbreviation', 6);
			$table->decimal('time_start', 11, 0);
			$table->integer('gmt_offset');
			$table->char('dst', 1);
			$table->index(['zone_id','time_start']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tz_timezones');
	}

}
