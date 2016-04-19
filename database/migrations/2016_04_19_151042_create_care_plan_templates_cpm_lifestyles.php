<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarePlanTemplatesCpmLifestyles extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('care_plan_templates_cpm_lifestyles', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('care_plan_template_id');
			$table->foreign('care_plan_template_id')
					->references('id')
					->on('care_plan_templates')
					->onUpdate('cascade')
					->onDelete('cascade');

			$table->unsignedInteger('cpm_lifestyle_id');
			$table->foreign('cpm_lifestyle_id')
				->references('id')
				->on('cpm_lifestyles')
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
		Schema::drop('care_plan_templates_cpm_lifestyles');
	}

}
