<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQAImportSummariesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('q_a_import_summaries');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('q_a_import_summaries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ccda_id')->unsigned()->nullable()->index('q_a_import_summaries_ccda_id_foreign');
            $table->boolean('flag');
            $table->integer('duplicate_id')->unsigned()->nullable();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->string('location')->nullable();
            $table->boolean('hasName');
            $table->integer('medications')->unsigned();
            $table->integer('problems')->unsigned();
            $table->integer('allergies')->unsigned();
            $table->timestamps();
            $table->boolean('has_phone');
            $table->boolean('has_street_address');
            $table->boolean('has_zip');
            $table->boolean('has_city');
            $table->boolean('has_state');
        });
    }
}
