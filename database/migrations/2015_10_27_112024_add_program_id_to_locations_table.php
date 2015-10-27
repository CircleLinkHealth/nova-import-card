<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProgramIdToLocationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('locations', function(Blueprint $table)
		{
			if( ! Schema::hasColumn('locations', 'program_id')) {
				$table->integer('program_id')->unsigned();

				$table->connection('mysql_no_prefix')->foreign('program_id')->references('blog_id')->on('wp_blogs');
			}
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('locations', function(Blueprint $table)
		{
			if( Schema::hasColumn('locations', 'program_id')) {
				$table->integer('program_id')->unsigned();
			}
		});
	}

}
