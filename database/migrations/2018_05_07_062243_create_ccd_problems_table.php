<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdProblemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccd_problems', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_monitored')->default(0)->comment('A monitored problem is a problem we provide Care Management for.');
            $table->integer('problem_import_id')->unsigned()->nullable()->index('ccd_problems_problem_import_id_foreign');
            $table->integer('ccda_id')->nullable()->index('ccd_problems_ccda_id_foreign');
            $table->integer('patient_id')->unsigned()->index('ccd_problems_patient_id_foreign');
            $table->integer('ccd_problem_log_id')->nullable()->index('ccd_problems_ccd_problem_log_id_foreign');
            $table->text('name', 65535)->nullable();
            $table->boolean('billable')->nullable();
            $table->integer('cpm_problem_id')->nullable()->index('ccd_problems_cpm_problem_id_foreign');
            $table->integer('cpm_instruction_id')->nullable()->comment('A pointer to an instruction for the ccd problem');
            $table->softDeletes();
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
        Schema::drop('ccd_problems');
    }
}
