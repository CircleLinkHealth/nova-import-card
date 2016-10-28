<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProgramUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

        // ensure wp_users.id matches up
		Schema::connection('mysql_no_prefix')->table('lv_locations', function($table)
		{
			$table->dropForeign('lv_locations_program_id_foreign');
		});
		Schema::connection('mysql_no_prefix')->table('lv_locations', function($table)
		{
			$table->integer('program_id', false)->change();
		});

        // change wp_blogs.id for proper foreign key
		Schema::connection('mysql_no_prefix')->table('wp_blogs', function($table)
		{
            $table->integer('id', false)->unsigned()->change();
		});

		// Create table for associating programs to users (Many-to-Many)
		Schema::connection('mysql_no_prefix')->create('lv_program_user', function (Blueprint $table) {
			$table->integer('user_id')->unsigned();
			$table->integer('program_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('wp_users');
            $table->foreign('program_id')->references('id')->on('wp_blogs');

			$table->primary(['user_id', 'program_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('lv_program_user');
	}

}
