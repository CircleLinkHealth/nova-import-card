<?php

use App\awvPatients;
use App\User;
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
    {//use awvPatientsSeeder - it will create users also
        factory(User::class, 49)->create();
    }
}
