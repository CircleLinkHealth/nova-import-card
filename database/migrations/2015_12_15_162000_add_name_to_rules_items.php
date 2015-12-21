<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameToRulesItems extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('mysql_no_prefix')->table('rules_items', function(Blueprint $table)
		{
			if( ! Schema::connection('mysql_no_prefix')->hasColumn('rules_items', 'name' )){
				$table->string('care_item_id')->after('qid');
				$table->string('name')->after('care_item_id');
				$table->string('display_name')->after('name');
				$table->string('description')->after('display_name');
			}

			if( ! Schema::connection('mysql_no_prefix')->hasColumn('rules_items', 'deleted_at' )) {
				$table->softDeletes();
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
		Schema::connection('mysql_no_prefix')->table('rules_items', function(Blueprint $table)
		{
			if( Schema::connection('mysql_no_prefix')->hasColumn('rules_items', 'name' )){
				$table->dropColumn('care_item_id');
				$table->dropColumn('name');
				$table->dropColumn('display_name');
				$table->dropColumn('description');
			}

			if( Schema::connection('mysql_no_prefix')->hasColumn('rules_items', 'deleted_at' )){
				$table->dropSoftDeletes();
			}
		});
	}

}
