<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
				->references('blog_id')
				->on('wp_blogs')
				->onUpdate('cascade');
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
		});
	}

}
