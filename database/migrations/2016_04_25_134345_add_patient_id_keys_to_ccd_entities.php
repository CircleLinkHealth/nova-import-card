<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatientIdKeysToCcdEntities extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            (new \App\Entities\CCD\CcdAllergy())->getTable(),
            (new \App\Entities\CCD\CcdMedication())->getTable(),
            (new \App\Entities\CCD\CcdProblem())->getTable(),
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function ($table) use ($tableName){
                if (!Schema::hasColumn($tableName, 'patient_id')) {
                    $table->unsignedInteger('patient_id')->after('ccda_id');
                    $table->foreign('patient_id')
                        ->references('id')
                        ->on((new \App\User())->getTable())
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
                }
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
        $tables = [
            (new \App\Entities\CCD\CcdAllergy())->getTable(),
            (new \App\Entities\CCD\CcdMedication())->getTable(),
            (new \App\Entities\CCD\CcdProblem())->getTable(),
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function ($table) use ($tableName){
                if (Schema::hasColumn($tableName, 'patient_id'))
                {
                    $table->dropForeign("{$tableName}_patient_id_foreign");
                    $table->dropColumn('patient_id');
                }
            });
        }
    }

}
