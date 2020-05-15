<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use Tests\TestCase;

class CareAmbassadorPanelAPI extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        //assert action routes work

        //assert search works

        //assert correct messages if things go wrong

        //assert all keys exist in the resouce

        //assert show route

        //CA Directors

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
