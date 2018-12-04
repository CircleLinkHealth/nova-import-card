<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCarePlanTemplatesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('care_plan_templates', function (Blueprint $table) {
            $table->dropForeign('care_plan_templates_program_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('care_plan_templates', function (Blueprint $table) {
            $table->foreign('program_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
