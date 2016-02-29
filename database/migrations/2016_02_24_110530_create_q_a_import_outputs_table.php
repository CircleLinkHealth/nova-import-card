<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQAImportOutputsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('q_a_import_outputs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('ccda_id');
			$table->longText('output');
			$table->timestamps();

			$table->foreign('ccda_id')
				->references('id')
				->on('ccdas')
				->onUpdate('cascade')
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
		Schema::drop('q_a_import_outputs');
	}

}
