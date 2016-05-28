<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeprCareItemId extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$tables = [
			(new \App\Models\CPM\CpmBiometric())->getTable(),
			(new \App\Models\CPM\CpmMisc())->getTable(),
		];

		foreach ($tables as $tableName)
		{
			Schema::table($tableName, function ($table) use ($tableName){
				if (! Schema::hasColumn($tableName, 'care_item_id'))
				{
					$table->unsignedInteger('care_item_id')->after('id')->nullable();
					$table->foreign('care_item_id')
						->references('id')
						->on((new \App\CareItem())->getTable())
						->onUpdate('cascade');
				}
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
		$tables = [
			(new \App\Models\CPM\CpmBiometric())->getTable(),
			(new \App\Models\CPM\CpmMisc())->getTable(),
		];
		
		foreach ($tables as $tableName)
		{
			Schema::table($tableName, function ($table) use ($tableName){
				if (Schema::hasColumn($tableName, 'care_item_id'))
				{
					$table->dropForeign("{$tableName}_care_item_id_foreign");
					$table->dropColumn('care_item_id');
				}
			});
		}
	}

}
