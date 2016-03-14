<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeRevisionsTablePrefix extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('lv_revisions'))
		{
			Schema::rename('lv_revisions', 'revisions');
			//Schema::dropIfExists('migrations');
		}

		if (!Schema::hasTable('revisions')) {
			Schema::create('revisions', function ($table) {
				$table->increments('id');
				$table->string('revisionable_type');
				$table->integer('revisionable_id');
				$table->integer('user_id')->nullable();
				$table->string('key');
				$table->text('old_value')->nullable();
				$table->text('new_value')->nullable();
				$table->timestamps();
				$table->index(array('revisionable_id', 'revisionable_type'));
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
		//Schema::rename('revisions', 'lv_revisions');
	}

}
