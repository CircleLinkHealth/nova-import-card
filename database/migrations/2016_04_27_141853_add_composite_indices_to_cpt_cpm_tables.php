<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompositeIndicesToCptCpmTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('care_plan_templates_cpm_lifestyles', function(Blueprint $table)
		{
			$table->index(['care_plan_template_id', 'cpm_lifestyle_id'], 'cpt_id_lifestyles_index');
		});

		Schema::table('care_plan_templates_cpm_symptoms', function(Blueprint $table)
		{
			$table->index(['care_plan_template_id', 'cpm_symptom_id'], 'cpt_id_symptoms_index');
		});

		Schema::table('care_plan_templates_cpm_medication_groups', function(Blueprint $table)
		{
			$table->index(['care_plan_template_id', 'cpm_medication_group_id'], 'med_grp_cpt_index');
		});

		Schema::table('care_plan_templates_cpm_problems', function(Blueprint $table)
		{
			$table->index(['care_plan_template_id', 'cpm_problem_id'], 'cpt_id_problems_index');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('SET foreign_key_checks = 0');
    
		Schema::table('care_plan_templates_cpm_lifestyles', function(Blueprint $table)
		{
			$table->dropIndex('cpt_id_lifestyles_index');
		});

		Schema::table('care_plan_templates_cpm_symptoms', function(Blueprint $table)
		{
			$table->dropIndex('cpt_id_symptoms_index');
		});

		Schema::table('care_plan_templates_cpm_medication_groups', function(Blueprint $table)
		{
			$table->dropIndex('med_grp_cpt_index');
		});

		Schema::table('care_plan_templates_cpm_problems', function(Blueprint $table)
		{
			$table->dropIndex('cpt_id_problems_index');
		});

		DB::statement('SET foreign_key_checks = 1');

	}

}
