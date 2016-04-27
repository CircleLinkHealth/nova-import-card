<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarePlanTemplatesCpmMiscs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('care_plan_templates_cpm_miscs', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('care_plan_template_id');
			$table->foreign('care_plan_template_id')
				->references('id')
				->on('care_plan_templates')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->unsignedInteger('cpm_misc_id');
			$table->foreign('cpm_misc_id')
				->references('id')
				->on((new \App\Models\CPM\CpmMisc())->getTable())
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->timestamps();

			$table->index(['care_plan_template_id', 'cpm_misc_id'], 'cpt_id_cpm_misc_id_index');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('care_plan_templates_cpm_miscs');
	}

}
