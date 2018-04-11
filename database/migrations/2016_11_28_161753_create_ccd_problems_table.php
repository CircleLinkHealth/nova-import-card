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
            $table->integer('ccda_id')->nullable()->index('ccd_problems_ccda_id_foreign');
            $table->integer('patient_id')->unsigned()->index('ccd_problems_patient_id_foreign');
            $table->integer('vendor_id')->nullable()->index('ccd_problems_vendor_id_foreign');
            $table->integer('ccd_problem_log_id')->nullable()->index('ccd_problems_ccd_problem_log_id_foreign');
            $table->text('name', 65535)->nullable();
            $table->string('code')->nullable();
            $table->string('code_system')->nullable();
            $table->string('code_system_name')->nullable();
            $table->boolean('activate');
            $table->integer('cpm_problem_id')->nullable()->index('ccd_problems_cpm_problem_id_foreign');
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
