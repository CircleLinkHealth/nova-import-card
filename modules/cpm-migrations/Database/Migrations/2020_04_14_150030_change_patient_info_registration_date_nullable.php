<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePatientInfoRegistrationDateNullable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        config(['database.connections.mysql.strict' => false]);
        DB::reconnect();

        try {
            DB::table('patient_info')
                ->whereIn('registration_date', [
                    //any empty strings would be changed to this after we convert
                    '0000-00-00 00:00:00',
                    //still leave this in case
                    '',
                ])
                ->update(['registration_date' => null]);

            Schema::table('patient_info', function (Blueprint $table) {
                $table->dateTime('registration_date')->nullable()->change();
            });
        } catch (Exception $e) {
            Log::warning($e->getMessage());
        }
    }
}
