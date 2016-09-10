<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeNoCallsSinceNullableOnPatientInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            if (Schema::hasColumn('patient_info', 'no_call_attempts_since_last_success')) {
                $table->dropColumn('no_call_attempts_since_last_success');
            }
        });

        Schema::table('patient_info', function (Blueprint $table) {
            if (!Schema::hasColumn('patient_info', 'no_call_attempts_since_last_success')) {
                $table->integer('no_call_attempts_since_last_success')
                    ->nullable()
                    ->after('last_successful_contact_time');
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
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn('attempt_note');

        });
    }
}
