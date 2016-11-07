<?php

use Illuminate\Database\Migrations\Migration;

class RemoveRevisionsPrefixCheck extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('lv_revisions') && ! Schema::hasTable('revisions'))
		{
			Schema::rename('lv_revisions', 'revisions');
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// cant reverse this
	}

}
