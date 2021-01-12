<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProblemImportsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('problem_imports');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('problem_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('medical_record_type')->nullable();
            $table->integer('medical_record_id')->unsigned()->nullable();
            $table->integer('imported_medical_record_id')->unsigned()->index('problem_imports_imported_medical_record_id_foreign');
            $table->integer('vendor_id')->unsigned()->nullable()->index('problem_imports_vendor_id_foreign');
            $table->integer('ccd_problem_log_id')->unsigned()->index('problem_imports_ccd_problem_log_id_foreign');
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('code_system')->nullable();
            $table->string('code_system_name')->nullable();
            $table->boolean('activate');
            $table->integer('cpm_problem_id')->unsigned()->nullable()->index('problem_imports_cpm_problem_id_foreign');
            $table->integer('substitute_id')->unsigned()->nullable()->index('problem_imports_substitute_id_foreign');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
