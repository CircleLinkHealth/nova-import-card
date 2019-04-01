<?php

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Seeder;

class PracticeTableSeeder extends Seeder
{
    public function run()
    {
        \DB::table('practices')->delete();

        factory(Practice::class, 5)->create()->each(function ($practice) {
            $practice->active = 1;
            $practice->save();
        });
    }
}
