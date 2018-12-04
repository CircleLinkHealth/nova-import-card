<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
        Schema::create('revisions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('revisionable_type');
            $table->integer('revisionable_id');
            $table->integer('user_id')->nullable();
            $table->string('key');
            $table->text('old_value', 65535)->nullable();
            $table->text('new_value', 65535)->nullable();
            $table->string('ip')->nullable();
            $table->boolean('is_phi')->default(false);
            $table->timestamps();
            $table->index(['revisionable_id', 'revisionable_type']);
        });
    }
}
