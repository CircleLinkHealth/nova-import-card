<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTzZonesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tz_zones', function(Blueprint $table)
		{
			$table->foreign('country_code')->references('country_code')->on('tz_countries')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tz_zones', function(Blueprint $table)
		{
			$table->dropForeign('tz_zones_country_code_foreign');
		});
	}

}
