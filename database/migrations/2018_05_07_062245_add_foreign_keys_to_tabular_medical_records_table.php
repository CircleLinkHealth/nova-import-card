<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTabularMedicalRecordsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tabular_medical_records', function(Blueprint $table)
		{
			$table->foreign('billing_provider_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('location_id')->references('id')->on('locations')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('patient_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('practice_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('uploaded_by')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tabular_medical_records', function(Blueprint $table)
		{
			$table->dropForeign('tabular_medical_records_billing_provider_id_foreign');
			$table->dropForeign('tabular_medical_records_location_id_foreign');
			$table->dropForeign('tabular_medical_records_patient_id_foreign');
			$table->dropForeign('tabular_medical_records_practice_id_foreign');
			$table->dropForeign('tabular_medical_records_uploaded_by_foreign');
		});
	}

}
