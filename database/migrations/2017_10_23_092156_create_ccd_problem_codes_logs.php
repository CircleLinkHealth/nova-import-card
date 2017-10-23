<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcdProblemCodesLogs extends Migration
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
            $table->unsignedInteger('ccd_problem_log_id')->nullable();
            $table->string('code_system_name', 20);
            $table->string('code_system_oid', 50)->nullable();
            $table->string('code', 20);
            $table->timestamps();

            $table->foreign('ccd_problem_log_id')
                ->references('id')
                ->on('ccd_problem_logs')
                ->onDelete('cascade')
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
        Schema::dropIfExists('ccd_problem_codes_logs');
    }
}
