<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToPatientCcmStatusRevisionsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_ccm_status_revisions', function (Blueprint $table) {
            $table->dropIndex('created_at_index');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_ccm_status_revisions', function (Blueprint $table) {
            $table->index(['created_at'], 'created_at_index');
        });
    }
}
