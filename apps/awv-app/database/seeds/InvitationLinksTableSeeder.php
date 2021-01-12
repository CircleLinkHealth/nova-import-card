<?php

use App\InvitationLink;
use Illuminate\Database\Seeder;

class InvitationLinksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(InvitationLink::class, 40)->create();
    }
}
