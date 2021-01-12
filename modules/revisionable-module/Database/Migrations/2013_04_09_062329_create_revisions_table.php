<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class CreateRevisionsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('revisions');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasTable('revisions')) {
            return;
        }
        Schema::create('revisions', function ($table) {
            $table->increments('id');
            $table->string('revisionable_type');
            $table->integer('revisionable_id');
            $table->integer('user_id')->nullable();
            $table->string('key');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();

            $table->index(['revisionable_id', 'revisionable_type']);
        });
    }
}
