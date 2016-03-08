<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToProblemsLog extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ccd_problem_logs', function(Blueprint $table)
		{
			$table->unsignedInteger('cpm_problem_id')->after('invalid')->nullable()->default(null);

			$table->foreign( 'cpm_problem_id' )
				->references( 'id' )
				->on( 'cpm_problems' )
				->onUpdate( 'cascade' )
				->onDelete( 'cascade' );
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ccd_problem_logs', function(Blueprint $table)
		{
			$table->removeColumn('cpm_problem_id');
		});
	}

}
