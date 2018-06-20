<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTzZonesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tz_zones', function(Blueprint $table)
		{
			$table->increments('zone_id');
			$table->string('country_code')->index('tz_zones_country_code_foreign');
			$table->string('zone_name');
			$table->index(['zone_name','country_code']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tz_zones');
	}

}
