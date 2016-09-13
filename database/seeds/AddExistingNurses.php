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

            \App\State::whereCode('NY')->value('id'),
            \App\State::whereCode('FL')->value('id'),
            \App\State::whereCode('MD')->value('id')

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

            \App\State::whereCode('FL')->value('id'),
            \App\State::whereCode('NC')->value('id'),

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

            \App\State::whereCode('FL')->value('id'),

        ]);

        //Sue Logan
        $nurses[3] = \App\NurseInfo::create([

            'user_id' => 1877,
            'status' => 'active',
            'hourly_rate' => 20,
            'spanish' => false,

        ]);

        $nurses[3]->states()->attach([

            \App\State::whereCode('MO')->value('id'),

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
//            \App\State::whereCode('FL'),
//            \App\State::whereCode('PR')
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
            \App\State::whereCode('FL')->value('id'),
            \App\State::whereCode('GA')->value('id'),
            \App\State::whereCode('CA')->value('id'),
            \App\State::whereCode('CT')->value('id'),
            \App\State::whereCode('AR')->value('id'),
            \App\State::whereCode('IL')->value('id'),
            \App\State::whereCode('IN')->value('id'),
            \App\State::whereCode('IA')->value('id'),
            \App\State::whereCode('KY')->value('id'),
            \App\State::whereCode('KS')->value('id'),
            \App\State::whereCode('MS')->value('id'),
            \App\State::whereCode('MA')->value('id'),
            \App\State::whereCode('MN')->value('id'),
            \App\State::whereCode('MI')->value('id'),
            \App\State::whereCode('NC')->value('id'),
            \App\State::whereCode('NV')->value('id'),
            \App\State::whereCode('NY')->value('id'),
            \App\State::whereCode('OR')->value('id'),
            \App\State::whereCode('OK')->value('id'),
            \App\State::whereCode('PA')->value('id'),
            \App\State::whereCode('TN')->value('id'),
            \App\State::whereCode('TX')->value('id'),
            \App\State::whereCode('VA')->value('id'),
            \App\State::whereCode('WA')->value('id'),

        ]);


        //Cheryl McLaughlin
        $nurses[6] = \App\NurseInfo::create([

            'user_id' => 2356,
            'status' => 'active',
            'hourly_rate' => 20,
            'spanish' => '',

        ]);

        $nurses[6]->states()->attach([

            \App\State::whereCode('NJ')->value('id'),
            \App\State::whereCode('PA')->value('id'),
        ]);


    }
}
