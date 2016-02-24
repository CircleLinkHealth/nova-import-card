<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQAImportSummariesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('q_a_import_summaries', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('qa_output_id');
			$table->string('duplicate_ids')->nullable();
			$table->boolean('hasName');
			$table->unsignedInteger('medications');
			$table->unsignedInteger('problems');
			$table->unsignedInteger('allergies');
			$table->timestamps();

			$table->foreign('qa_output_id')
				->references('id')
				->on('q_a_import_outputs')
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
		Schema::drop('q_a_import_summaries');
	}

}
