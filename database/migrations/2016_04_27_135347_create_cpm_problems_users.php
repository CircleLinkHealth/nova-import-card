<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpmProblemsUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cpm_problems_users', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('patient_id');
			$table->foreign('patient_id')
				->references('id')
				->on((new \App\User())->getTable())
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->unsignedInteger('cpm_problem_id');
			$table->foreign('cpm_problem_id')
				->references('id')
				->on('cpm_problems')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->timestamps();

			$table->unique(['patient_id', 'cpm_problem_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cpm_problems_users');
	}

}
