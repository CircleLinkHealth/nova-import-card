<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProviderToQaSummaries extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('q_a_import_summaries', function(Blueprint $table)
		{
			$table->string('provider')->nullable()->after('name');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('q_a_import_summaries', function(Blueprint $table)
		{
			$table->removeColumn('provider');
		});
	}

}
