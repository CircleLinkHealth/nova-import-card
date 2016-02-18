<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCcdImportRoutinesStrategies extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ccd_import_routines_strategies', function(Blueprint $table)
		{
			$table->dropForeign('ccd_import_routines_strategies_ccd_import_routine_id_foreign');

			$table->foreign('ccd_import_routine_id')
				->references('id')
				->on('ccd_import_routines')
				->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ccd_import_routines_strategies', function(Blueprint $table)
		{
			//
		});
	}

}
