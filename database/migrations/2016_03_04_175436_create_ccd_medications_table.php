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
		Schema::create('ccd_medication_logs', function(Blueprint $table)
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

			$table->string('reference')->nullable()->default(null);
			$table->string('reference_title')->nullable()->default(null);
			$table->string('reference_sig')->nullable()->default(null);
			$table->string('start')->nullable()->default(null);
			$table->string('end')->nullable()->default(null);
			$table->string('status')->nullable()->default(null);
			$table->string('text')->nullable()->default(null);

			$table->string('product_name')->nullable()->default(null);
			$table->string('product_code')->nullable()->default(null);
			$table->string('product_code_system')->nullable()->default(null);
			$table->string('product_text')->nullable()->default(null);

			$table->string('translation_name')->nullable()->default(null);
			$table->string('translation_code')->nullable()->default(null);
			$table->string('translation_code_system')->nullable()->default(null);
			$table->string('translation_code_system_name')->nullable()->default(null);

			$table->boolean('import');
			$table->boolean('invalid');

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
		Schema::drop('ccd_medication_logs');
	}

}
