<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToDemographicsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ccd_demographics_logs', function(Blueprint $table)
		{
			$table->string('language')->after('email')->nullable()->default( null );
			$table->string('street2')->after('street')->nullable()->default( null );
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ccd_demographics_logs', function(Blueprint $table)
		{
			$table->removeColumn('language');
			$table->removeColumn('street2');
		});
	}

}
