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
		// lv_care_items
		Schema::connection('mysql_no_prefix')->create('lv_care_items', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('parent_id');
			$table->string('name');
			$table->string('display_name');
			$table->string('description');
			$table->timestamps();
		});

		// lv_care_plans
		Schema::connection('mysql_no_prefix')->create('lv_care_plans', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('type'); // template, provider_default, patient_default
			$table->string('display_name'); // Provider Default, Patient Careplan
			$table->string('description');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('program_id');
			$table->timestamps();
		});

		// lv_care_plan_sections
		Schema::connection('mysql_no_prefix')->create('lv_care_plan_sections', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('plan_id');
			$table->string('display_name');
			$table->string('description');
			$table->foreign('plan_id')->references('blog_id')->on('wp_blogs');
			$table->timestamps();
		});

		// lv_care_plan_care_item - table for associating plan to items (Many-to-Many)
		Schema::connection('mysql_no_prefix')->create('lv_care_plan_care_item', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('plan_id');
			$table->unsignedInteger('item_id');
			$table->unsignedInteger('plan_section_id');
			$table->string('status');
			$table->string('value');
			$table->string('meta_key');
			$table->string('meta_value');
			$table->string('ui_placeholder');
			$table->string('ui_default');
			$table->string('ui_title');
			$table->string('ui_fld_type');
			$table->string('ui_show_detail');
			$table->string('ui_row_start');
			$table->string('ui_row_end');
			$table->string('ui_col_start');
			$table->string('ui_col_end');
			$table->foreign('plan_id')->references('id')->on('lv_care_plans');
			$table->foreign('item_id')->references('id')->on('lv_care_items');
			$table->foreign('plan_section_id')->references('id')->on('lv_care_plan_sections');
			$table->unique(['plan_id', 'item_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::connection('mysql_no_prefix')->drop('lv_care_plan_care_item');
		Schema::connection('mysql_no_prefix')->drop('lv_care_plan_sections');
		Schema::connection('mysql_no_prefix')->drop('lv_care_items');
		Schema::connection('mysql_no_prefix')->drop('lv_care_plans');
	}

}
