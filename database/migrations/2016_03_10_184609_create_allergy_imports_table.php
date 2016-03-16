<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllergyImportsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('allergy_imports', function(Blueprint $table)
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

			$table->unsignedInteger( 'ccd_allergy_log_id' );
			$table->foreign( 'ccd_allergy_log_id' )
				->references( 'id' )
				->on( 'ccd_allergy_logs' )
				->onUpdate( 'cascade' )
				->onDelete( 'cascade' );

			$table->string('allergen_name')->nullable()->default(null);

			$table->unsignedInteger('substitute_id')->nullable()->default(null);
			$table->foreign( 'substitute_id' )
				->references( 'id' )
				->on( 'allergy_imports' );

			$table->softDeletes();

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
		Schema::dropIfExists('allergy_imports');
	}

}
