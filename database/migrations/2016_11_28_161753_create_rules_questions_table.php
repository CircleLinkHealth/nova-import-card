<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRulesQuestionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rules_questions', function (Blueprint $table) {
            $table->bigInteger('qid', true);
            $table->string('msg_id', 45)->index('idx_rules_questions_msg_id');
            $table->string('qtype', 45)->nullable()->default('List');
            $table->string('obs_key')->nullable();
            $table->text('description', 65535)->nullable();
            $table->string('icon', 10);
            $table->string('category', 10);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rules_questions');
    }
}
