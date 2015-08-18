<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQliqsoftMessagesLog extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('qliqsoft_messages_log', function(Blueprint $table)
		{
			$table->increments('id');
            $table->text('to');
            $table->text('message');
            $table->text('status');
            $table->text('conversation_id');
            $table->text('message_id');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('qliqsoft_messages_log');
	}

}
