<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcdMedicationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ccd_medications', function(Blueprint $table)
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
				->onUpdate( 'cascade' );

			$table->unsignedInteger( 'ccd_medication_log_id' );
			$table->foreign( 'ccd_medication_log_id' )
				->references( 'id' )
				->on( 'ccd_medication_logs' )
				->onUpdate( 'cascade' );

			$table->unsignedInteger( 'medication_group_id' )->nullable();
			$table->foreign( 'medication_group_id', 'medication_group_foreign' )
				->references( 'id' )
				->on( 'cpm_medication_groups' )
				->onUpdate( 'cascade' );

			$table->string('name')->nullable()->default(null);
			$table->string('sig')->nullable()->default(null);

			$table->string('code')->nullable()->default(null);
			$table->string('code_system')->nullable()->default(null);
			$table->string('code_system_name')->nullable()->default(null);

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
		Schema::drop('ccd_medications');
	}

}
