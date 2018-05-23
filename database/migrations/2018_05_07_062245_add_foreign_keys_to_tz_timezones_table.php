<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTzTimezonesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tz_timezones', function(Blueprint $table)
		{
			$table->foreign('zone_id')->references('zone_id')->on('tz_zones')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tz_timezones', function(Blueprint $table)
		{
			$table->dropForeign('tz_timezones_zone_id_foreign');
		});
	}

}
