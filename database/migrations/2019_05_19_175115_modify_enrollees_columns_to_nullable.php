<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyEnrolleesColumnsToNullable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if ( ! isUnitTestingEnv()) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            Schema::table(
                'enrollees',
                function (Blueprint $table) {
                    $table->dropForeign('enrollees_cpm_problem_1_foreign');
                }
            );
        }

        Schema::table(
            'enrollees',
            function (Blueprint $table) {
                $table->string('referring_provider_name', 255)->nullable()->change();
                $table->string('problems', 255)->nullable()->change();
                $table->unsignedInteger('cpm_problem_1')->nullable()->change();
            }
        );

        Schema::table(
            'enrollees',
            function (Blueprint $table) {
                $table->foreign('cpm_problem_1')
                    ->references('id')
                    ->on('cpm_problems')
                    ->onUpdate('cascade');
            }
        );

        if ( ! isUnitTestingEnv()) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
