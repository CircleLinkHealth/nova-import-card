<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCcmConditions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            if (Schema::hasColumn('enrollees', 'ccm_condition_1')) {
                $table->dropColumn('ccm_condition_1');
            }

            if (Schema::hasColumn('enrollees', 'ccm_condition_2')) {
                $table->dropColumn('ccm_condition_2');
            }
        });

        Schema::table('enrollees', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollees', 'cpm_problem_1')) {
                $table->unsignedInteger('cpm_problem_1');

                $table->foreign('cpm_problem_1')
                    ->references('id')
                    ->on('cpm_problems')
                    ->onUpdate('cascade');
            }

            if (!Schema::hasColumn('enrollees', 'cpm_problem_2')) {
                $table->unsignedInteger('cpm_problem_2');

                $table->foreign('cpm_problem_2')
                    ->references('id')
                    ->on('cpm_problems')
                    ->onUpdate('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            //
        });
    }
}
