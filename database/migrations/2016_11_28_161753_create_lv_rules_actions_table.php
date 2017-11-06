<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvRulesActionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lv_rules_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('action_name', 50);
            $table->string('action', 50)->nullable();
            $table->string('action_description', 200)->nullable();
            $table->string('active', 1)->default('Y');
            $table->string('multiple_return', 1)->default('Y');
            $table->integer('created_by')->unsigned();
            $table->integer('modified_by')->unsigned();
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
        Schema::drop('lv_rules_actions');
    }
}
