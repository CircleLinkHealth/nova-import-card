<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcdProblemCodeLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_problem_code_logs', function (Blueprint $table) {
            $table->foreign('ccd_problem_log_id')->references('id')->on('ccd_problem_logs')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('problem_code_system_id')->references('id')->on('problem_code_systems')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccd_problem_code_logs', function (Blueprint $table) {
            $table->dropForeign('ccd_problem_code_logs_ccd_problem_log_id_foreign');
            $table->dropForeign('ccd_problem_code_logs_problem_code_system_id_foreign');
        });
    }
}
