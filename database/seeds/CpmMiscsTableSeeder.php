<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Models\CPM\CpmMisc;
use Illuminate\Database\Seeder;

class CpmMiscsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            'Allergies',
            'Appointments',
            'Full Conditions List',
            'Medication List',
            'Other',
            'Social Services',
        ] as $misc) {
            CpmMisc::create(
                [
                    'name' => $misc,
                ]
            );
        }
    }
}
