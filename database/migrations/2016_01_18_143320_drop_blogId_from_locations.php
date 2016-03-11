<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropBlogIdFromLocations extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if(Schema::hasColumn('lv_locations', 'program_id'))
		{
			Schema::table('lv_locations', function(Blueprint $table)
			{
				$table->dropColumn('program_id');
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
		Schema::table('lv_locations', function(Blueprint $table)
		{
			//
		});
	}

}
