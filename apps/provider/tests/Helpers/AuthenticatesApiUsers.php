<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Helpers;

trait AuthenticatesApiUsers
{
    protected $accessToken;

    public function authenticate()
    {
        //Authenticate using location 40 (Treat and Release)
        $response = $this->call('POST', '/api/v1.0/oauth/access_token', [
            'username' => 'upg10@clh.com',
            'password' => 'zeGNTgy3gD8heGQC',
        ]);

        $this->assertResponseOk();

        $this->accessToken = json_decode($response->getContent(), true)['access_token'];
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
}
