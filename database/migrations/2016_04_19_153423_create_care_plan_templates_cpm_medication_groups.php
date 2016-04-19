<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarePlanTemplatesCpmMedicationGroups extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('care_plan_templates_cpm_medication_groups', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('care_plan_template_id');
			$table->foreign('care_plan_template_id', 'cpt_id_cpt_id_rel_foreign')
				->references('id')
				->on('care_plan_templates')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->unsignedInteger('cpm_medication_group_id');
			$table->foreign('cpm_medication_group_id', 'cpm_medi_groups_rel_foreign')
				->references('id')
				->on('cpm_medication_groups')
				->onUpdate('cascade')
				->onDelete('cascade');

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
		Schema::drop('care_plan_templates_cpm_medication_groups');
	}

}
