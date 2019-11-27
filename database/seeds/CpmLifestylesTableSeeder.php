<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Models\CPM\CpmLifestyle;
use Illuminate\Database\Seeder;

class CpmLifestylesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            'Diabetic Diet',
            'Exercise',
            'Healthy Diet',
            'Low Salt Diet',
        ] as $lifestyle) {
            CpmLifestyle::create(['name' => $lifestyle]);
        }
    }
}
