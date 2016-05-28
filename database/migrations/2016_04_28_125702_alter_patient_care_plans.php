<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPatientCarePlans extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('patient_care_plans', function(Blueprint $table)
		{
			$table->unsignedInteger('provider_approver_id')->nullable()->change();
			$table->unsignedInteger('qa_approver_id')->nullable()->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('patient_care_plans', function(Blueprint $table)
		{
			//
		});
	}

}
