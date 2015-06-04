<?php namespace database\seeds;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\User;

class UserTableSeeder extends Seeder {

    public function run()
    {
        DB::table('users')->delete();

        User::create([
        'name' => 'Michalis Antoniou',
        'email' => 'mantoniou@circlelinkhealth.com',
        'password' => Hash::make('iamadmin')
        ]);

        User::create([
        'name' => 'Phil Lawlor',
        'email' => 'PLawlor@circlelinkhealth.com',
        'password' => Hash::make('iamadmin')
        ]);
    }

}