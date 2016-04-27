<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCareItemsMap extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('care_items', function(Blueprint $table)
		{
			$table->string('type')->after('id')->nullable();
			$table->unsignedInteger('type_id')->after('id')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('care_items', function(Blueprint $table)
		{
			$table->dropColumn('type');
			$table->dropColumn('type_id');
		});
	}

}
