<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodeSystemToCodesLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_problem_code_logs', function (Blueprint $table) {
            $table->unsignedInteger('problem_code_system_id')
                  ->nullable()
                  ->after('id');

            $table->foreign('problem_code_system_id')
                  ->references('id')
                  ->on('problem_code_systems')
                  ->onUpdate('cascade');
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
            //
        });
    }
}
