<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDemographicsImportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demographics_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ccda_id')->unsigned()->index();
            $table->integer('vendor_id')->unsigned()->index('demographics_imports_vendor_id_foreign');
            $table->integer('program_id')->unsigned()->nullable()->index('demographics_imports_program_id_foreign');
            $table->integer('provider_id')->unsigned()->nullable()->index('demographics_imports_provider_id_foreign');
            $table->integer('location_id')->unsigned()->nullable()->index('demographics_imports_location_id_foreign');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('mrn_number')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip', 5)->nullable();
            $table->string('cell_phone', 12)->nullable();
            $table->string('home_phone', 12)->nullable();
            $table->string('work_phone', 12)->nullable();
            $table->string('email')->nullable();
            $table->string('preferred_contact_timezone')->nullable();
            $table->string('consent_date')->nullable();
            $table->string('preferred_contact_language')->nullable();
            $table->string('study_phone_number')->nullable();
            $table->integer('substitute_id')->unsigned()->nullable()->index('demographics_imports_substitute_id_foreign');
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
