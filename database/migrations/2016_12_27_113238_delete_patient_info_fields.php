<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeletePatientInfoFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('patient_info', 'careplan_provider_approver')) {
            return true;
        }

        Schema::table('patient_info', function (Blueprint $table) {
            $table->dropColumn('careplan_provider_approver');
            $table->dropColumn('careplan_qa_approver');
            $table->dropColumn('careplan_status');
            $table->dropColumn('careplan_qa_date');
            $table->dropColumn('careplan_provider_date');
            $table->dropColumn('careplan_last_printed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            //
        });
    }
}
