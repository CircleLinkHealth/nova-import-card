<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSourceToCcdas extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ccdas', function(Blueprint $table)
		{
			$table->string('source')->after('vendor_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ccdas', function(Blueprint $table)
		{
			$table->dropColumn('source');
		});
	}

}
