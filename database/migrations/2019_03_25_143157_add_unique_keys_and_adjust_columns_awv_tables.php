<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueKeysAndAdjustColumnsAwvTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('questions', function (Blueprint $table) {
            $table->text('body')->unique()->change();
        });

        Schema::table('question_types_answers', function (Blueprint $table) {
            $table->string('value')->nullable()->change();
        });

        Schema::table('surveys', function (Blueprint $table) {
            $table->unique('name');
        });

        Schema::table('survey_instances', function (Blueprint $table) {
            $table->unique('name');
        });

        Schema::table('survey_questions', function (Blueprint $table) {
            $table->unique(['survey_instance_id', 'order', 'sub_order'], 'survey_instance_order_unique');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropUnique('questions_body_unique');
        });

        Schema::table('surveys', function (Blueprint $table) {
            $table->dropUnique('surveys_name_unique');
        });

        Schema::table('survey_instances', function (Blueprint $table) {
            $table->dropUnique('survey_instances_name_unique');
        });

        Schema::table('survey_questions', function (Blueprint $table) {
            $table->dropUnique('survey_instance_order_unique');
        });

    }
}
