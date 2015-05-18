<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaActivityMetaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ma_activity_meta', function(Blueprint $table)
		{
			$table->increments('meta_id');
			$table->unsignedInteger('act_id');
			$table->unsignedInteger('comment_id');
			$table->string('message_id', 30);
			$table->string('meta_key', 255)->nullable();
			$table->longText('meta_value');
			$table->timestamps();
			$table->softDeletes();

//			$table->unique(['act_id', 'meta_key'], 'act_id_2'); Removed for when adding many comments at once
			$table->index('meta_key', 'meta_key');
			$table->index('act_id', 'act_id');

			$table->foreign('act_id')->references('act_id')->on('ma_activities');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('ma_activity_meta');
	}

}
