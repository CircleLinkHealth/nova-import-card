<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPagetimerColumns extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('page_timer', function($table)
		{
			// $table->string('activity_type')->after('url_short');
			// $table->string('title')->after('activity_type');
			$table->string('query_string')->after('title');
		});
		Schema::table('activities', function($table)
		{
			$table->unsignedInteger('page_timer_id');
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
