<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddsClientNameToApiKeysTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('api_keys', function(Blueprint $table)
		{
			if ( ! Schema::hasColumn('api_keys', 'client_name')) {
				$table->string('client_name');
			}
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('api_keys', function(Blueprint $table)
		{
			if ( ! Schema::hasColumn('api_keys', 'client_name')) {
				$table->dropColumn('client_name');
			}
		});
	}

}
