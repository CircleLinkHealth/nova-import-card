<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImportValidationFieldsToXmlCcds extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('xml_ccds', function(Blueprint $table)
		{
			if (! Schema::hasColumn('xml_ccds', 'patient_name')) {
				$table->string('patient_name')->after('id');
			}
			if (! Schema::hasColumn('xml_ccds', 'patient_dob')) {
				$table->string('patient_dob')->after('patient_name');
			}
//			$table->unique(['patient_name', 'patient_dob']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('xml_ccds', function(Blueprint $table)
		{
			if (Schema::hasColumn('xml_ccds', 'patient_name')) {
				$table->dropColumn('patient_name');
			}
			if (Schema::hasColumn('xml_ccds', 'patient_dob')) {
				$table->dropColumn('patient_dob');
			}
		});
	}

}
