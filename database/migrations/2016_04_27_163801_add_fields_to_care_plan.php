<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToCarePlan extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('patient_care_plans', function(Blueprint $table)
		{
			$table->boolean('track_care_transitions')
				->nullable()
				->after('medications_list');

			$table->text('old_meds_list')
				->nullable()
				->after('medications_list');

			$table->text('social_services')
				->nullable()
				->after('medications_list');

			$table->text('appointments')
				->nullable()
				->after('medications_list');

			$table->text('other')
				->nullable()
				->after('medications_list');


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
			$table->dropColumn('track_care_transitions');
			$table->dropColumn('old_meds_list');
			$table->dropColumn('social_services');
			$table->dropColumn('appointments');
			$table->dropColumn('other');
		});
	}

}
