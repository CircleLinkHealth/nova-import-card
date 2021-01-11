<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Database\Seeders;

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
