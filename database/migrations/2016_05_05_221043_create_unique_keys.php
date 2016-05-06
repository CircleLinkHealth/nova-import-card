<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUniqueKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //recreate the tables to add unique vs index
        if (!app()->environment('production')) {
            DB::table('lv_migrations')
                ->whereIn('migration', [
                    '2016_04_27_135326_create_cpm_symptoms_users',
                    '2016_04_27_135347_create_cpm_problems_users',
                    '2016_04_27_135405_create_cpm_medication_groups_users',
                    '2016_04_27_135424_create_cpm_lifestyles_users',
                    '2016_04_27_180446_create_cpm_miscs_users',
                    '2016_05_03_170901_create_cpm_biometrics_users'
                ])->delete();

            $tables = [
                'cpm_symptoms_users',
                'cpm_problems_users',
                'cpm_medication_groups_users',
                'cpm_lifestyles_users',
                'cpm_miscs_users',
                'cpm_biometrics_users'
            ];

            DB::statement('SET foreign_key_checks = 0');

            foreach ($tables as $t) {
                Schema::dropIfExists($t);
            }

            DB::statement('SET foreign_key_checks = 1');

            DB::table('lv_migrations')
                ->insert([
                    'migration' => '2016_05_05_221043_create_unique_keys',
                    'batch' => 1
                ]);

            Artisan::call('migrate');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
