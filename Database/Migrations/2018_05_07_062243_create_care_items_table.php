<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCareItemsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('care_items');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('care_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model_field_name')->nullable();
            $table->integer('type_id')->unsigned()->nullable();
            $table->string('type')->nullable();
            $table->string('relationship_fn_name');
            $table->integer('parent_id')->unsigned();
            $table->integer('qid')->unsigned()->index();
            $table->string('obs_key');
            $table->string('name')->unique();
            $table->string('display_name');
            $table->string('description');
            $table->timestamps();
        });
    }
}
