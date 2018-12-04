<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdProblemCodeLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccd_problem_code_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('problem_code_system_id')->unsigned()->nullable()->index('ccd_problem_code_logs_problem_code_system_id_foreign');
            $table->integer('ccd_problem_log_id')->unsigned()->nullable()->index('ccd_problem_code_logs_ccd_problem_log_id_foreign');
            $table->string('code_system_name', 20);
            $table->string('code_system_oid', 50)->nullable();
            $table->string('code', 20);
            $table->string('name', 150)->nullable();
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
        Schema::drop('ccd_problem_code_logs');
    }
}
