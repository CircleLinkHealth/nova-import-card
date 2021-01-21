<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwoFA\Tests;

use CircleLinkHealth\Customer\Entities\User;
use Tests\TestCase;

class AuthyTest extends TestCase
{
    public function test_it_creates_one_touch_request()
    {
        $user = factory(User::class)->create();
        auth()->login($user);

        $response = $this->json('POST', route('user.2fa.store'), [
            'country_code'   => '357',
            'phone_number'   => '97722289',
            'is_2fa_enabled' => true,
            'method'         => 'app',
        ]);

        $response->assertStatus(201);

        $response = $this->json('POST', route('user.2fa.one-touch-request.create'));

        $response->assertStatus(200);
    }

    public function test_it_receives_errors()
    {
        $user = factory(User::class)->create();
        auth()->login($user);

        $response = $this->json('POST', route('user.2fa.store'), [
            'country_code'   => '1111111111',
            'phone_number'   => '123',
            'is_2fa_enabled' => true,
            'method'         => 'app',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'errors' => [
                'country_code',
                'phone_number',
            ],
        ]);
    }

    public function test_it_registers_a_user()
    {
        $user = factory(User::class)->create();
        auth()->login($user);

        $response = $this->json('POST', route('user.2fa.store'), [
            'country_code'   => '357',
            'phone_number'   => '97722289',
            'is_2fa_enabled' => true,
            'method'         => 'app',
        ]);

        $response->assertStatus(201);
    }
}
