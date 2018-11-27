<?php

namespace Tests\Feature;

use App\Contracts\TwoFactorAuthenticationApi;
use Authy\AuthyApi;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthyTest extends TestCase
{
    /**
     * @var AuthyService
     */
    private $service;

    protected function setUp()
    {
        parent::setUp();

        $this->api     = $this->app->make(AuthyApi::class);
        $this->service = $this->app->make(AuthyService::class);
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

        $response = $this->json('POST', route('user.2fa.approval-request.create'));

        $response->assertStatus(200);
    }
}
