<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEnrolleesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->dropForeign('enrollees_batch_id_foreign');
            $table->dropForeign('enrollees_care_ambassador_id_foreign');
            $table->dropForeign('enrollees_cpm_problem_1_foreign');
            $table->dropForeign('enrollees_cpm_problem_2_foreign');
            $table->dropForeign('enrollees_provider_id_foreign');
            $table->dropForeign('enrollees_user_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->foreign('batch_id')->references('id')->on('eligibility_batches')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('care_ambassador_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cpm_problem_1')->references('id')->on('cpm_problems')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('cpm_problem_2')->references('id')->on('cpm_problems')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('provider_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
