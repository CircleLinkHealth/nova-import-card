<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemImportsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('problem_imports', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('ccda_id');
			$table->foreign('ccda_id')
				->references('id')
				->on('ccdas')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->unsignedInteger( 'vendor_id' );
			$table->foreign( 'vendor_id' )
				->references( 'id' )
				->on( 'ccd_vendors' )
				->onUpdate( 'cascade' )
				->onDelete( 'cascade' );

			$table->unsignedInteger( 'ccd_problem_log_id' );
			$table->foreign( 'ccd_problem_log_id' )
				->references( 'id' )
				->on( 'ccd_problem_logs' )
				->onUpdate( 'cascade' )
				->onDelete( 'cascade' );

			$table->string('name')->nullable()->default(null);
			$table->string('code')->nullable()->default(null);
			$table->string('code_system')->nullable()->default(null);
			$table->string('code_system_name')->nullable()->default(null);

			$table->boolean('edited');

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('problem_imports');
	}

}
