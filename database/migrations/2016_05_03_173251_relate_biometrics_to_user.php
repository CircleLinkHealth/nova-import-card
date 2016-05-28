<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RelateBiometricsToUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            (new \App\Models\CPM\Biometrics\CpmBloodPressure())->getTable(),
            (new \App\Models\CPM\Biometrics\CpmBloodSugar())->getTable(),
            (new \App\Models\CPM\Biometrics\CpmSmoking())->getTable(),
            (new \App\Models\CPM\Biometrics\CpmWeight())->getTable(),
        ];

        foreach ($tables as $t)
        {
            if (Schema::hasColumn($t, 'patient_id')) continue;
            
            Schema::table($t, function(Blueprint $table){
                $table->unsignedInteger('patient_id')->after('id');
                $table->foreign('patient_id')
                    ->references('id')
                    ->on((new \App\User())->getTable())
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
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
            (new \App\Models\CPM\Biometrics\CpmBloodPressure())->getTable(),
            (new \App\Models\CPM\Biometrics\CpmBloodSugar())->getTable(),
            (new \App\Models\CPM\Biometrics\CpmSmoking())->getTable(),
            (new \App\Models\CPM\Biometrics\CpmWeight())->getTable(),
        ];
        DB::statement('SET foreign_key_checks = 0');

        foreach ($tables as $t)
        {
            
            Schema::table($t, function(Blueprint $table){
                $table->dropForeign(['patient_id']);
                $table->dropColumn('patient_id');
            });

        }

        DB::statement('SET foreign_key_checks = 1');

    }
}
