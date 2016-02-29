<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameToQAImportSummaries extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('q_a_import_summaries', function(Blueprint $table)
		{
			if (Schema::hasColumn('q_a_import_summaries', 'hasName'))
			{
				$table->removeColumn('hasName');
				$table->string('name')->after('duplicate_ids');
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
		Schema::table('q_a_import_summaries', function(Blueprint $table)
		{
			//
		});
	}

}
