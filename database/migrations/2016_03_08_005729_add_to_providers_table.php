<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToProvidersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ccd_provider_logs', function(Blueprint $table)
		{
			$table->string('organization')->after('last_name')->nullable()->default(null);
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
			$table->dropColumn('organization');
		});
	}

}
