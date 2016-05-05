<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/5/16
 * Time: 6:09 PM
 */
class TruncateCpmRelationshipTables extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $tables = [
            'cpm_biometrics_users',
            'cpm_lifestyles_users',
            'cpm_medication_groups_users',
            'cpm_miscs_users',
            'cpm_problems_users',
            'cpm_symptoms_users',
        ];

        foreach ($tables as $table)
        {
            DB::table($table)->truncate();
        }
    }
}