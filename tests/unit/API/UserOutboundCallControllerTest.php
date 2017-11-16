<?php

namespace Tests\Unit\API;

use App\Call;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class UserOutboundCallControllerTest extends TestCase
{
    use DatabaseTransactions,
        WithoutMiddleware;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_it_assigns_calls_to_a_nurse()
    {
        //setup
        $patient = factory(User::class)->create();
        $call = Call::create(['inbound_cpm_id' => $patient->id]);
        $nurse = factory(User::class)->create();
        $admin = factory(User::class)->create();
        $admin->roles()->attach(1);
        auth()->login($admin);

        //trigger
        $response = $this->json('POST', route('user.outbound-calls.store', ['user' => $nurse->id]),
            ['callIds' => $call->id]);

        //assert
        $response->assertStatus(200);

        $this->assertEquals(1, $nurse->outboundCalls->count());
    }
}
