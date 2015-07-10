<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rules', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('rule_name', 50);
			$table->string('rule_description', 200)->nullable();
			$table->string('active', 1)->default('N');
			$table->string('type_id', 1)->nullable();
			$table->unsignedInteger('sort');
			$table->timestamp('effective_date');
			$table->timestamp('expiration_date');
			$table->text('summary')->nullable();
			$table->string('approve', 1)->default('N');
			$table->string('archive', 1)->default('N');
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('modified_by');
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
		Schema::drop('rules');
	}

}
