<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CcdDocumentLogsAddType extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ccd_document_logs', function(Blueprint $table)
		{
			$table->string('type')->after('vendor_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ccd_document_logs', function(Blueprint $table)
		{
			$table->removeColumn('type');
		});
	}

}
