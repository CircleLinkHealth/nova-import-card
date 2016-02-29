<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcdasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ccdas', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('vendor_id');
			$table->longText('xml');
			$table->longText('json');
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('user_id')
				->references('ID')
				->on('wp_users')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->foreign('vendor_id')
				->references('id')
				->on('ccd_vendors')
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
		Schema::drop('ccdas');
	}

}
