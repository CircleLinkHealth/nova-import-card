<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientReportsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('patient_reports', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('patient_id');
			$table->unsignedInteger('patient_mrn');
			$table->string('provider_id');
			$table->string('file_type');
			$table->string('file_path');

			$table->foreign('patient_id')
				->references('ID')
				->on('wp_users')
				->onDelete('cascade')
				->onUpdate('cascade');

			$table->softDeletes();
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
		Schema::drop('patient_reports');
	}

}
