<?php

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {//use PatientsTableSeeder - it will create users also
        factory(User::class, 40)->create();
    }
}
