<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixBillableProblemKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->dropForeign(['problem_1']);
            $table->dropForeign(['problem_2']);

            $table->foreign('problem_1')
                  ->references('id')
                  ->on('ccd_problems')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->foreign('problem_2')
                  ->references('id')
                  ->on('ccd_problems')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            //
        });
    }
}
