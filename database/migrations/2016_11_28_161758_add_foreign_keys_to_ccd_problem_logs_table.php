<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcdProblemLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_problem_logs', function (Blueprint $table) {
            $table->foreign('cpm_problem_id')->references('id')->on('cpm_problems')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccd_problem_logs', function (Blueprint $table) {
            $table->dropForeign('ccd_problem_logs_cpm_problem_id_foreign');
        });
    }

}
