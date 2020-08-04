<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientCcmStatusRevisionsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_ccm_status_revisions');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_ccm_status_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('patient_info_id')->nullable();
            $table->unsignedInteger('patient_user_id')->nullable();
            $table->string('action')->nullable();
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('patient_info_id')
                ->references('id')
                ->on('patient_info')
                ->onDelete('set null');

            $table->foreign('patient_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
}
