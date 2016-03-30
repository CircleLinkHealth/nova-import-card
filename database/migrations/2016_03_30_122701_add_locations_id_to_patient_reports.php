<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationsIdToPatientReports extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('patient_reports', function(Blueprint $table)
		{
			$table->unsignedInteger('location_id')->after('file_path');
			$table->foreign('location_id')
				->references('id')
				->on('lv_locations')
				->onDelete('cascade')
				->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('patient_reports', function(Blueprint $table)
		{
			//
		});
	}

}
