<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Seeder;

class EnrolleesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $enrollees = factory(Enrollee::class, 100)->create();


        //take some enrollees to fake "suggested" family members for testing purposes. Randomly make their data look like the would be family members
        list($enrollees, $fakeSuggestedFamilyMembers) = $enrollees->partition(function ($e) use ($enrollees) {
            return $enrollees->search($e) < 20;
        });

        if ( ! empty($enrollees)) {
            $fakeSuggestedFamilyMembers->each(function ($e) use ($enrollees) {
                $family = $enrollees->random();

                $rand = rand(0, 10);
                switch ($rand) {
                    case ($rand < 2) :
                        $e->address = $family->address;
                        break;
                    case ($rand >= 2 && $rand < 5) :
                        $e->address_2 = $family->address;
                        break;
                    case ($rand >= 5 && $rand < 7):
                        $e->home_phone = $family->cell_phone;
                        break;
                    case ($rand >= 7 && $rand < 9):
                        $e->cell_phone = $family->home_phone;
                        break;
                    case ($rand == 10):
                        $e->other_phone = $family->cell_phone;
                }
                $e->save();
            });
        }

    }
}
