<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultToLastQuestionAnsweredIdInUsersSurveys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_surveys', function (Blueprint $table) {
            $table->integer('last_question_answered_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_surveys', function (Blueprint $table) {
            $table->integer('last_question_answered_id')->default(NULL)->change();
        });
    }
}
