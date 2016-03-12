<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDemographicsImportsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('demographics_imports', function(Blueprint $table)
		{
			$table->increments('id');

			$table->unsignedInteger( 'ccda_id' );
			$table->foreign( 'ccda_id' )
				->references( 'id' )
				->on( 'ccdas' )
				->onUpdate( 'cascade' )
				->onDelete( 'cascade' );

			$table->unsignedInteger( 'vendor_id' );
			$table->foreign( 'vendor_id' )
				->references( 'id' )
				->on( 'ccd_vendors' )
				->onUpdate( 'cascade' )
				->onDelete( 'cascade' );

			$table->unsignedInteger( 'provider_id' );
			$table->foreign( 'provider_id' )
				->references( 'id' )
				->on( 'wp_users' );

			$table->unsignedInteger( 'location_id' );
			$table->foreign( 'location_id' )
				->references( 'id' )
				->on( 'lv_locations' );

			$table->string( 'first_name' )->nullable()->default( null );
			$table->string( 'last_name' )->nullable()->default( null );
			$table->string( 'dob' )->nullable()->default( null );
			$table->string( 'gender' )->nullable()->default( null );
			$table->string( 'mrn_number' )->nullable()->default( null );
			$table->string( 'street' )->nullable()->default( null );
			$table->string( 'city' )->nullable()->default( null );
			$table->string( 'state' )->nullable()->default( null );
			$table->string( 'zip', 5 )->nullable()->default( null );
			$table->string( 'cell_phone', 12 )->nullable()->default( null );
			$table->string( 'home_phone', 12 )->nullable()->default( null );
			$table->string( 'work_phone', 12 )->nullable()->default( null );
			$table->string( 'email' )->nullable()->default( null );

			$table->unsignedInteger('substitute_id')->nullable()->default(null);
			$table->foreign( 'substitute_id' )
				->references( 'id' )
				->on( 'demographics_imports' );

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
		Schema::drop('demographics_imports');
	}

}
