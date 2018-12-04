<?php

namespace Tests\AprimaApi;

use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/12/16
 * Time: 4:50 PM
 */
class UploadCcdTest extends TestCase
{
    use WithoutMiddleware;

    public function test_no_credentials_error()
    {
        $response = $this->call('POST', '/api/v1.0/ccd', []);

        $response->assertStatus(403);
    }

    public function test_ccd_with_provider_info()
    {
        $response = $this->withSession(['apiUser' => User::find(1811)])
                         ->call('POST', route('api.aprima.uploadCcd'), [
                             'file'     => base64_encode('suppose this is a ccd'),
                             'provider' => \GuzzleHttp\json_encode([
                                 'providerId' => '1',
                                 'lastName'   => 'smith',
                                 'firstName'  => 'john',
                                 'phone'      => '111-111-1111',
                                 'address'    => [
                                     'line1' => '111 main st',
                                     'line2' => "222 main st",
                                     'city'  => 'somewhere',
                                     'state' => 'TX',
                                     'zip'   => '11111',
                                 ],
                                 'clinic'     => 'testClinic',
                             ]),
                         ]);

        $response->assertStatus(201);
    }
}
