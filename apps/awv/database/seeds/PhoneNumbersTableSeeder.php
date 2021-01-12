<?php

use CircleLinkHealth\Customer\Entities\PhoneNumber;
use Illuminate\Database\Seeder;

class PhoneNumbersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(PhoneNumber::class, 40)->create();
    }
}
