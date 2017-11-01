<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRulesQuestionSetsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rules_question_sets', function (Blueprint $table) {
            $table->bigInteger('qsid', true)->unsigned();
            $table->bigInteger('provider_id');
            $table->string('qs_type', 45)->nullable()->default('RPT');
            $table->integer('qs_sort');
            $table->bigInteger('qid')->nullable();
            $table->bigInteger('answer_response')->nullable();
            $table->bigInteger('aid')->nullable();
            $table->bigInteger('low')->nullable();
            $table->bigInteger('high')->nullable();
            $table->string('action', 200)->nullable()->default("fxGoto('')");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rules_question_sets');
    }
}
