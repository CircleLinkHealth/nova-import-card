<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultInstructionsForUserCpm extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cpm_symptoms_users', function (Blueprint $table) {
			$table->text('default_instructions')
				->nullable()
				->default(null)
				->before('created_at');
		});

		Schema::table('cpm_problems_users', function (Blueprint $table) {
			$table->text('default_instructions')
				->nullable()
				->default(null)
				->before('created_at');
		});

		Schema::table('cpm_medication_groups_users', function (Blueprint $table) {
			$table->text('default_instructions')
				->nullable()
				->default(null)
				->before('created_at');
		});

		Schema::table('cpm_lifestyles_users', function (Blueprint $table) {
			$table->text('default_instructions')
				->nullable()
				->default(null)
				->before('created_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cpm_symptoms_users', function(Blueprint $table)
		{
			$table->dropColumn('default_instructions');
		});

		Schema::table('cpm_problems_users', function(Blueprint $table)
		{
			$table->dropColumn('default_instructions');
		});

		Schema::table('cpm_medication_groups_users', function(Blueprint $table)
		{
			$table->dropColumn('default_instructions');
		});

		Schema::table('cpm_lifestyles_users', function(Blueprint $table)
		{
			$table->dropColumn('default_instructions');
		});
	}

}
