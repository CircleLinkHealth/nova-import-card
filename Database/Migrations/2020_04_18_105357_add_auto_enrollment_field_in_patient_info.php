<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoEnrollmentFieldInPatientInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('patient_info', 'auto_enrollment_triggered')) {
            Schema::table('patient_info', function (Blueprint $table) {
                $table->boolean('auto_enrollment_triggered')->default(false);
            });

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            if (Schema::hasColumn('patient_info', 'auto_enrollment_triggered')) {
                $table->dropColumn('auto_enrollment_triggered');
            }
        });
    }
}
