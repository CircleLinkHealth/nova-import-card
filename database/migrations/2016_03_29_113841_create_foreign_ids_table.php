<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateForeignIdsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('foreign_ids', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('user_id');

			$table->string('foreign_id');

			$table->string('system');

			$table->timestamps();

				$table->unique(['user_id', 'foreign_id', 'system'], 'unique_triple');

				$table->foreign('user_id', 'user_id_foreign')
                    ->references('id')
					->on('wp_users')
					->onDelete('cascade')
					->onUpdate('cascade');
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
			$table->dropForeign('user_id_foreign');
			$table->dropIndex('unique_triple');
			$table->drop();
		});
	}

}
