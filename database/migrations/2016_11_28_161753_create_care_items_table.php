<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCareItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
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
            $table->integer('qid')->unsigned();
            $table->string('obs_key');
            $table->string('name')->unique();
            $table->string('display_name');
            $table->string('description');
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
        Schema::drop('care_items');
    }
}
