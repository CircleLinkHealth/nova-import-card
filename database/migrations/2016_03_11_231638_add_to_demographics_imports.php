<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddToDemographicsImports extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('demographics_imports', function(Blueprint $table)
		{
			$table->unsignedInteger('program_id')->after('vendor_id')->nullable();
			$table->foreign('program_id')
                ->references('id')
				->on('wp_blogs')
				->onUpdate('cascade');

			$table->string('study_phone_number')->after('email')->nullable();
			$table->string('preferred_contact_language')->after('email')->nullable();
			$table->string('consent_date')->after('email')->nullable();
			$table->string('preferred_contact_timezone')->after('email')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('demographics_imports', function(Blueprint $table)
		{
			$table->dropForeign('demographics_imports_program_id_foreign');
			$table->dropColumn('program_id');
			$table->dropColumn('study_phone_number');
			$table->dropColumn('preferred_contact_language');
			$table->dropColumn('consent_date');
			$table->dropColumn('preferred_contact_timezone');
		});
	}

}
