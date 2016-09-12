<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastContactTimeToPatientInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {

            if (!Schema::hasColumn('patient_info', 'last_contact_time')) {

                $table->datetime('last_contact_time')
                    ->after('last_successful_contact_time');
                $table->datetime('last_successful_contact_time')->change();

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

                $table->dropColumn('last_contact_time');

        });
    }
}
