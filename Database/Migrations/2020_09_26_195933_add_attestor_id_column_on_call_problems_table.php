<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttestorIdColumnOnCallProblemsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_problems', function (Blueprint $table) {
            $table->dropForeign(['attestor_id']);

            $table->dropColumn('attestor_id');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_problems', function (Blueprint $table) {
            $table->unsignedInteger('attestor_id')->after('patient_user_id')->nullable();

            $table->foreign('attestor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
}
