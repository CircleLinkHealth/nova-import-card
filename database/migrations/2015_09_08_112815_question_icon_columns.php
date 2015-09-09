<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class QuestionIconColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        // ensure wp_users.ID matches up
        Schema::connection('mysql_no_prefix')->table('rules_questions', function($table)
        {
            $table->string('icon', 10)->after('description');
            $table->string('category', 10)->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
    }
}
