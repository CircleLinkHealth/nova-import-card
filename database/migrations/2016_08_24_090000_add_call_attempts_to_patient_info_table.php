<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCallAttemptsToPatientInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {

            if (!Schema::hasColumn('patient_info', 'no_call_attempts_since_last_success')) {

                $table->integer('no_call_attempts_since_last_success')
                    ->default(0)
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
        Schema::table('patient_info', function (Blueprint $table) {

                $table->dropColumn('no_call_attempts_since_last_success');

        });
    }
}
