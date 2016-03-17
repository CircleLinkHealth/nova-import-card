<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedicationImportsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('medication_imports', function(Blueprint $table)
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

			$table->unsignedInteger( 'ccd_medication_log_id' );
			$table->foreign( 'ccd_medication_log_id' )
				->references( 'id' )
				->on( 'ccd_medication_logs' )
				->onUpdate( 'cascade' )
				->onDelete( 'cascade' );

			$table->string('name')->nullable()->default(null);
			$table->string('sig')->nullable()->default(null);

			$table->string('code')->nullable()->default(null);
			$table->string('code_system')->nullable()->default(null);
			$table->string('code_system_name')->nullable()->default(null);

			$table->unsignedInteger('substitute_id')->nullable()->default(null);
			$table->foreign( 'substitute_id' )
				->references( 'id' )
				->on( 'medication_imports' );

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
		Schema::dropIfExists('medication_imports');
	}

}
