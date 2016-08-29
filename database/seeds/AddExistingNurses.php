<?php

use Illuminate\Database\Seeder;

class AddExistingNurses extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $nurses = array();

        //Patricia Koeppel
        $nurses[0] = \App\NurseInfo::create([

            'user_id' => 1920,
            'status' => 'active',
            'hourly_rate' => 20,
            'spanish' => false,

        ]);

        //attach states
        $nurses[0]->states()->attach([

            \App\States::whereCode('NY')->value('id'),
            \App\States::whereCode('FL')->value('id'),
            \App\States::whereCode('MD')->value('id')

        ]);


//        Kathryn Zinmer (Alchalabi)
        $nurses[1] = \App\NurseInfo::create([

            'user_id' => 2159,
            'status' => 'active',
            'hourly_rate' => 20,
            'spanish' => false,
            'isNLC' => true

        ]);

        $nurses[1]->states()->attach([

            \App\States::whereCode('FL')->value('id'),
            \App\States::whereCode('NC')->value('id'),

        ]);

        //Lydia Kennedy
        $nurses[2] = \App\NurseInfo::create([

            'user_id' => 1755,
            'status' => 'active',
            'hourly_rate' => 20,
            'spanish' => false,
            'isNLC' => false


        ]);

        $nurses[2]->states()->attach([

            \App\States::whereCode('FL')->value('id'),

        ]);

        //Sue Logan
        $nurses[3] = \App\NurseInfo::create([

            'user_id' => 1877,
            'status' => 'active',
            'hourly_rate' => 20,
            'spanish' => false,

        ]);

        $nurses[3]->states()->attach([

            \App\States::whereCode('MO')->value('id'),

        ]);

//        //Helen Cruz
//        $nurses[4] = \App\NurseInfo::create([
//
//            'user_id' => 1877,
//            'status' => 'active',
//            'hourly_rate' => 20,
//            'spanish' => true,
//
//        ]);
//
//        $nurses[4]->states()->saveMany([
//
//            \App\States::whereCode('FL'),
//            \App\States::whereCode('PR')
//
//        ]);

        //Monique Porter
        $nurses[5] = \App\NurseInfo::create([

            'user_id' => 2332,
            'status' => 'active',
            'hourly_rate' => 20,
            'spanish' => false,

        ]);

        $nurses[5]->states()->attach([
            \App\States::whereCode('FL')->value('id'),
            \App\States::whereCode('GA')->value('id'),
            \App\States::whereCode('CA')->value('id'),
            \App\States::whereCode('CT')->value('id'),
            \App\States::whereCode('AR')->value('id'),
            \App\States::whereCode('IL')->value('id'),
            \App\States::whereCode('IN')->value('id'),
            \App\States::whereCode('IA')->value('id'),
            \App\States::whereCode('KY')->value('id'),
            \App\States::whereCode('KS')->value('id'),
            \App\States::whereCode('MS')->value('id'),
            \App\States::whereCode('MA')->value('id'),
            \App\States::whereCode('MN')->value('id'),
            \App\States::whereCode('MI')->value('id'),
            \App\States::whereCode('NC')->value('id'),
            \App\States::whereCode('NV')->value('id'),
            \App\States::whereCode('NY')->value('id'),
            \App\States::whereCode('OR')->value('id'),
            \App\States::whereCode('OK')->value('id'),
            \App\States::whereCode('PA')->value('id'),
            \App\States::whereCode('TN')->value('id'),
            \App\States::whereCode('TX')->value('id'),
            \App\States::whereCode('VA')->value('id'),
            \App\States::whereCode('WA')->value('id'),

        ]);


        //Cheryl McLaughlin
        $nurses[6] = \App\NurseInfo::create([

            'user_id' => 2356,
            'status' => 'active',
            'hourly_rate' => 20,
            'spanish' => '',

        ]);

        $nurses[6]->states()->attach([

            \App\States::whereCode('NJ')->value('id'),
            \App\States::whereCode('PA')->value('id'),
        ]);


    }
}
