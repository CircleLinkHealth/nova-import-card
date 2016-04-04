<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProviderIdToLog extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ccd_provider_logs', function(Blueprint $table)
		{
			$table->string('provider_id')
				->nullable()
				->after('npi');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ccd_provider_logs', function(Blueprint $table)
		{
			$table->dropColumn('provider_id');
		});
	}

}
