<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCascadesToCpmpromblemsFk extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cpm_problems', function(Blueprint $table)
		{
			if (!Schema::hasColumn('cpm_problems', 'care_item_name')) {
				$table->dropForeign('cpm_problems_care_item_name_foreign');
				$table->foreign('care_item_name', 'care_item_foreign')
					->references('name')
					->on('care_items')
					->onUpdate('cascade');
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
		Schema::table('cpm_problems', function(Blueprint $table)
		{
			//
		});
	}

}
