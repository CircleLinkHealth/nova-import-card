<?php

use App\CarePlan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateCarePlanTemplates extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('care_plan_templates', function(Blueprint $table)
		{
			$existing_templates = CarePlan::all();
			foreach($existing_templates as $existing_template){
				$care_plan_template = new App\CarePlanTemplate();
				$care_plan_template->display_name = $existing_template->display_name;
				$care_plan_template->program_id = $existing_template->program_id;
				$care_plan_template->save();
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
		Schema::table('care_plan_templates', function(Blueprint $table)
		{
			//
		});
	}

}
