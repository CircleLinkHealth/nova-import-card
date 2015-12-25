<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarePlanTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// care_items
		Schema::connection('mysql_no_prefix')->create('care_items', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('parent_id');
			$table->unsignedInteger('qid');
			$table->string('obs_key');
			$table->string('name');
			$table->string('display_name');
			$table->string('description');
			$table->unique('name');
			$table->timestamps();
		});

		// care_plans
		Schema::connection('mysql_no_prefix')->create('care_plans', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('parent_id');
			$table->string('name');
			$table->string('type'); // template, provider_default, patient_default
			$table->string('display_name'); // Provider Default, Patient Careplan
			$table->string('description');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('program_id');
			$table->timestamps();
		});

		// care_plan_sections
		Schema::connection('mysql_no_prefix')->create('care_sections', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('display_name');
			$table->string('description');
			$table->string('alert_key');
			$table->unique('name');
			$table->timestamps();
		});

		/*
		// care_plan_care_section - table for associating plan to sections (Many-to-Many)
		Schema::connection('mysql_no_prefix')->create('care_plan_care_section', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('plan_id');
			$table->unsignedInteger('section_id');
			$table->string('status');
			$table->foreign('plan_id')->references('id')->on('care_plans');
			$table->foreign('section_id')->references('id')->on('care_sections');
			$table->unique(['plan_id', 'section_id']);
		});
		*/

		// care_plan_care_section - table for associating plan to sections (Many-to-Many)
		Schema::connection('mysql_no_prefix')->create('care_plan_care_section', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('plan_id');
			$table->unsignedInteger('section_id');
			$table->string('status');
			$table->string('ui_sort');
			$table->foreign('plan_id')->references('id')->on('care_plans');
			$table->foreign('section_id')->references('id')->on('care_sections');
			$table->unique(['plan_id', 'section_id']);
		});

		// care_item_care_plan_care_section - table for associating plan to items (Many-to-Many)
		Schema::connection('mysql_no_prefix')->create('care_item_care_plan', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('item_id');
			$table->unsignedInteger('parent_id');
			$table->unsignedInteger('plan_id');
			$table->unsignedInteger('section_id');
			$table->string('meta_key');
			$table->string('meta_value');
			$table->string('alert_key');
			$table->string('ui_placeholder');
			$table->string('ui_default');
			$table->string('ui_title');
			$table->string('ui_fld_type');
			$table->string('ui_show_detail');
			$table->string('ui_row_start');
			$table->string('ui_row_end');
			$table->string('ui_sort');
			$table->string('ui_col_start');
			$table->string('ui_col_end');
			$table->string('ui_track_as_observation');
			$table->string('msg_app_en');
			$table->string('msg_app_es');
			$table->foreign('plan_id')->references('id')->on('care_plans');
			$table->foreign('item_id')->references('id')->on('care_items');
			$table->foreign('section_id')->references('id')->on('care_sections');
			$table->unique(['plan_id', 'item_id', 'section_id', 'parent_id'], 'plan_item_section_parent');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::connection('mysql_no_prefix')->drop('care_item_care_plan');
		//Schema::connection('mysql_no_prefix')->drop('care_plan_care_item');
		Schema::connection('mysql_no_prefix')->drop('care_plan_care_section');
		Schema::connection('mysql_no_prefix')->drop('care_sections');
		Schema::connection('mysql_no_prefix')->drop('care_items');
		Schema::connection('mysql_no_prefix')->drop('care_plans');
	}

}
