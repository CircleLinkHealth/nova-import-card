<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Ehr;
use Illuminate\Database\Seeder;

class EhrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Ehr::updateOrCreate([
            'name' => 'Athena',
        ], []);
    }
}
