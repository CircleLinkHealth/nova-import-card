<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientCarePlansTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('patient_care_plans', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('patient_id');
			$table->unsignedInteger('provider_approver_id');
			$table->unsignedInteger('qa_approver_id');
			$table->unsignedInteger('care_plan_template_id');

			$table->text('type');
			$table->text('status');

			$table->timestamp('qa_date');
			$table->timestamp('provider_date');

			$table->text('problems_list');
			$table->text('allergies_list');
			$table->text('medications_list');

			$table->timestamps();

			$table->foreign('patient_id')
				->references('ID')
				->on('wp_users')
				->onDelete('cascade')
				->onUpdate('cascade');

			$table->foreign('provider_approver_id')
				->references('ID')
				->on('wp_users')
				->onDelete('cascade')
				->onUpdate('cascade');

			$table->foreign('qa_approver_id')
				->references('ID')
				->on('wp_users')
				->onDelete('cascade')
				->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('patient_care_plans');
	}

}
