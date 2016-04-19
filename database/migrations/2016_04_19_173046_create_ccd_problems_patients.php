<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcdProblemsPatients extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ccd_problems_patients', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('patient_id');
			$table->foreign('patient_id')
				->references('id')
				->on('wp_users')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->unsignedInteger('ccd_problem_id');
			$table->foreign('ccd_problem_id')
				->references('id')
				->on('ccd_allergies')
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
		Schema::drop('ccd_problems_patients');
	}

}
