<?php

use App\SaasAccount;
use Illuminate\Database\Seeder;

class SaasAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SaasAccount::create(
            [
                'name' => 'CircleLink Health',
            ]
        );
    }
}
