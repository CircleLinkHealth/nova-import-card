<?php

use App\AwvUser;
use Illuminate\Database\Seeder;

class AwvUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(AwvUser::class, 100)->create();
    }
}
