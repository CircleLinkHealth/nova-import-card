<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImporterSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('importer_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('ehr_name');

			$table->string('allergiesListValidator');
			$table->string('allergiesListParser');
			$table->string('allergiesListImporter');

			$table->string('medicationsListValidator');
			$table->string('medicationsListParser');
			$table->string('medicationsListImporter');

			$table->string('problemsListValidator');
			$table->string('problemsListParser');
			$table->string('problemsListImporter');

			$table->string('problemsToMonitorValidator');
			$table->string('problemsToMonitorParser');
			$table->string('problemsToMonitorImporter');

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('importer_settings');
	}

}
