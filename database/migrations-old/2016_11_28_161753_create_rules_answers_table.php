<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRulesAnswersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rules_answers', function (Blueprint $table) {
            $table->bigInteger('aid', true);
            $table->string('value', 45);
            $table->text('alt_answers', 65535)->nullable();
            $table->integer('a_sort')->nullable()->default(1);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rules_answers');
    }
}
