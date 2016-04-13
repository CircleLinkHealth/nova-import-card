<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationToForeignId extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('foreign_ids', function(Blueprint $table)
		{
			if (!Schema::hasColumn('foreign_ids', 'location_id'))
			{
				$table->unsignedInteger('location_id')->after('user_id')->nullable();
				$table->foreign('location_id', 'location_foreign')
					->references('id')
					->on('lv_locations')
					->onDelete('cascade')
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
		Schema::table('foreign_ids', function(Blueprint $table)
		{
			$table->dropForeign('location_foreign');
			$table->dropColumn('location_id');
		});
	}

}
