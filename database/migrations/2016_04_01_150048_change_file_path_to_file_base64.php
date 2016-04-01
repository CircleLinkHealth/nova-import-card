<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFilePathToFileBase64 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('patient_reports', function(Blueprint $table)
		{
			$table->dropColumn('file_path');
			$table->mediumText('file_base64')->after('location_id');
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
