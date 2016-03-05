<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcdDocumentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ccd_document_logs', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger('ccda_id');
			$table->foreign('ccda_id')
				->references('id')
				->on('ccdas')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->unsignedInteger( 'vendor_id' );
			$table->foreign( 'vendor_id' )
				->references( 'id' )
				->on( 'ccd_vendors' )
				->onUpdate( 'cascade' )
				->onDelete( 'cascade' );

			$table->string('custodian');
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
		Schema::drop('ccd_document_logs');
	}

}
