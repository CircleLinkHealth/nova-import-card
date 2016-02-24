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
		Schema::connection('mysql_no_prefix')->create('lv_ccdas', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('qa_set_id');
			$table->unsignedInteger('ccd_vendor_id');
			$table->longText('xml');
			$table->longText('json');
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('user_id')
				->references('ID')
				->on('wp_users')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->foreign('ccd_vendor_id')
				->references('id')
				->on('lv_ccd_vendors')
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
