<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWelcomedDateToPatientInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {

            if (Schema::hasColumn('patient_info', 'date_welcomed')) {
                return;
            }

            $table->dateTime('date_welcomed')->nullable();

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

            $table->dropColumn('date_welcomed');

        });
    }
}
