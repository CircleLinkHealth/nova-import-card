<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsCcdaTypesToPatient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'patient_info',
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->unsignedInteger('imported_medical_record_id')
                    ->nullable()
                    ->after('id');

                $table->foreign('imported_medical_record_id')
                    ->references('id')
                    ->on('imported_medical_records')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
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
            //
        });
    }
}
