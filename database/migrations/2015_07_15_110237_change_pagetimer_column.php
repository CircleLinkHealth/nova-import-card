<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePagetimerColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('page_timer', function($table)
		{
			$table->dropColumn('rule_found');
			$table->unsignedInteger('rule_id')->nullable();
		});
		Schema::table('activities', function($table)
		{
			$table->unsignedInteger('page_timer_id')->nullable()->change();
		});
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
