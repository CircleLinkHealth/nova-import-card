<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveDefaultInstructions extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasColumn('cpm_problems', 'default_instructions')) {
			Schema::table('cpm_problems', function(Blueprint $table)
			{
				$table->dropColumn('default_instructions');
			});

			Schema::table('cpm_lifestyles', function(Blueprint $table)
			{
				$table->dropColumn('default_instructions');
			});

			Schema::table('cpm_medication_groups', function(Blueprint $table)
			{
				$table->dropColumn('default_instructions');
			});

			Schema::table('cpm_symptoms', function(Blueprint $table)
			{
				$table->dropColumn('default_instructions');
			});

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

			Schema::table('care_plan_templates_cpm_lifestyles', function(Blueprint $table)
			{
				$table->dropColumn('default_instructions');
			});

			Schema::table('care_plan_templates_cpm_symptoms', function(Blueprint $table)
			{
				$table->dropColumn('default_instructions');
			});

			Schema::table('care_plan_templates_cpm_medication_groups', function(Blueprint $table)
			{
				$table->dropColumn('default_instructions');
			});

			Schema::table('care_plan_templates_cpm_problems', function(Blueprint $table)
			{
				$table->dropColumn('default_instructions');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
