<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Seeder;

class PracticeTableSeeder extends Seeder
{
    public function run()
    {
        \DB::table('practices')->delete();

        factory(Practice::class, 2)->create(['active' => true])->each(function ($practice) {
            factory(Location::class)->create(['practice_id' => $practice->id, 'is_primary' => true]);
        });
    }
}
