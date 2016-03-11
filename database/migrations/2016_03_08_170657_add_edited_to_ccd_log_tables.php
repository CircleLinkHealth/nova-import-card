<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEditedToCcdLogTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$tables = [
			'ccd_document_logs',
			'ccd_provider_logs',
			'ccd_medication_logs',
			'ccd_allergy_logs',
			'ccd_problem_logs',
			'ccd_demographics_logs',
		];

		foreach ($tables as $table)
		{
			Schema::table($table, function(Blueprint $table)
			{
				$table->boolean('edited')->after('invalid');
			});
		}

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$tables = [
			'ccd_document_logs',
			'ccd_provider_logs',
			'ccd_medication_logs',
			'ccd_allergy_logs',
			'ccd_problem_logs',
			'ccd_demographics_logs',
		];

		foreach ($tables as $table)
		{
			Schema::table($table, function(Blueprint $table)
			{
				$table->removeColumn('edited');
			});
		}
	}

}
