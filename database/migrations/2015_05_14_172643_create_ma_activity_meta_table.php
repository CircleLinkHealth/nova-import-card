<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateMaActivityMetaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activitymeta', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('activity_id');
			$table->unsignedInteger('comment_id');
			$table->string('message_id', 30);

			$table->string('meta_key', 255)->nullable();
			$table->longText('meta_value');

			$table->timestamps();
			$table->softDeletes();

			$table->index('meta_key', 'meta_key');
			$table->index('activity_id', 'activity_id');

			$table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('SET FOREIGN_KEY_CHECKS = 0');
		Schema::dropIfExists('activitymeta');
		DB::statement('SET FOREIGN_KEY_CHECKS = 1');
	}

}
