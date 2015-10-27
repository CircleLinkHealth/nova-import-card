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
		Schema::connection('mysql_no_prefix')->table('lv_locations', function(Blueprint $table)
		{
			if( ! Schema::hasColumn('lv_locations', 'program_id')) {
				$table->bigInteger('program_id')->nullable();

				$table->foreign('program_id')->references('blog_id')->on('wp_blogs');
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
				$table->dropForeign('lv_locations_program_id_foreign');
				$table->dropColumn('program_id');

			}
		});
	}

}
