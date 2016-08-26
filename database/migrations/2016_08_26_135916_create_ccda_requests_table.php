<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcdaRequestsTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ccda_requests', function(Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('ccda_id')->nullable();
            $table->foreign('ccda_id')
                ->references('id')
                ->on('ccdas')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('vendor');

            //patient id on the api we are talking to
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('department_id');

            $table->boolean('successful_call')->nullable();

            $table->timestamps();

            $table->unique([
                'vendor',
                'patient_id',
            ]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ccda_requests');
	}

}
