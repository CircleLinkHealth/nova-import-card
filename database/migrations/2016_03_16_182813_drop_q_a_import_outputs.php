<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropQAImportOutputs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('q_a_import_outputs', function(Blueprint $table)
		{
			$table->drop();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('q_a_import_outputs', function(Blueprint $table)
		{
			//
		});
	}

}
