<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarePlanTemplatesCpmSymptoms extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('care_plan_templates_cpm_symptoms', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('care_plan_template_id');
			$table->foreign('care_plan_template_id')
				->references('id')
				->on('care_plan_templates')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->unsignedInteger('cpm_symptom_id');
			$table->foreign('cpm_symptom_id')
				->references('id')
				->on('cpm_symptoms')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->timestamps();

			$table->unique(['cpm_symptom_id', 'care_plan_template_id']);

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('care_plan_templates_cpm_symptoms');
	}

}
