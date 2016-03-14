<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarePlanTables3 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// care_item_user_values
		Schema::connection('mysql_no_prefix')->create('care_item_user_values', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('care_item_id');
			$table->unsignedInteger('user_id');
			$table->longText('value')->nullable();
			$table->foreign('care_item_id')->references('id')->on('care_items');
			$table->foreign('user_id')->references('id')->on('wp_users');
			$table->unique(['care_item_id', 'user_id'], 'plan_item_user');
		});

		// add care_plan-id to wp_users
		Schema::connection('mysql_no_prefix')->table('wp_users', function(Blueprint $table)
		{
			if( ! Schema::connection('mysql_no_prefix')->hasColumn('wp_users', 'care_plan_id')){
				$table->unsignedInteger('care_plan_id')->after('program_id');
			}
		});

		// change meta_value to long_text
		Schema::connection('mysql_no_prefix')->table('care_item_care_plan', function(Blueprint $table)
		{
			$table->longText('meta_value')->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::connection('mysql_no_prefix')->dropIfExists('care_item_user_values');
	}

}
