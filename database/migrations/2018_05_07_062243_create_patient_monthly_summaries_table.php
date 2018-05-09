<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientMonthlySummariesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('patient_monthly_summaries', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('patient_id')->unsigned();
			$table->integer('ccm_time')->unsigned()->default(0);
			$table->date('month_year');
			$table->integer('no_of_calls')->nullable();
			$table->integer('no_of_successful_calls')->nullable();
			$table->integer('problem_1')->unsigned()->nullable()->index('patient_monthly_summaries_problem_1_foreign');
			$table->integer('problem_2')->unsigned()->nullable()->index('patient_monthly_summaries_problem_2_foreign');
			$table->text('billable_problem1', 65535)->nullable();
			$table->text('billable_problem1_code', 65535)->nullable();
			$table->text('billable_problem2', 65535)->nullable();
			$table->text('billable_problem2_code', 65535)->nullable();
			$table->boolean('is_ccm_complex')->default(0);
			$table->boolean('approved')->default(0)->nullable();
			$table->boolean('rejected')->default(0)->nullable();
			$table->boolean('needs_qa')->nullable();
			$table->integer('actor_id')->unsigned()->nullable()->index('patient_monthly_summaries_actor_id_foreign');
			$table->timestamps();
			$table->unique(['patient_id','month_year']);
			$table->index(['patient_id','month_year','ccm_time']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('patient_monthly_summaries');
	}

}
