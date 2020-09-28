<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Tests;

use Aacotroneo\Saml2\Events\Saml2LogoutEvent;
use Aacotroneo\Saml2\Saml2Auth;
use Aacotroneo\Saml2\Saml2User;
use CircleLinkHealth\SamlSp\Console\RegisterSamlUserMapping;
use CircleLinkHealth\SamlSp\Entities\SamlUser;
use Mockery;
use OneLogin\Saml2\Auth as OneLogin_Saml2_Auth;
use Tests\CustomerTestCase;

class SamlSpTest extends CustomerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_authenticates_saml_user_from_saml_request()
    {
        $this->setUpMocks();
        \Artisan::call(RegisterSamlUserMapping::class, [
            'cpmUserId' => $this->superadmin()->id,
            'idp'       => 'testing',
            'idpUserId' => 'testUser',
        ]);

        $route = route('saml2_acs', [
            'idpName' => 'testing',
        ]);
        $this->post($route, [
            'SAMLResponse' => '',
        ]);
        self::assertAuthenticatedAs($this->superadmin());
    }

    public function test_it_authenticates_saml_user_from_saml_request_and_redirects_to_target_url()
    {
        $this->setUpMocks();
        \Artisan::call(RegisterSamlUserMapping::class, [
            'cpmUserId' => $this->superadmin()->id,
            'idp'       => 'testing',
            'idpUserId' => 'testUser',
        ]);

        $route = route('saml2_acs', [
            'idpName' => 'testing',
        ]);
        $targetRoute = route('admin.patientCallManagement.v2.index');
        $resp        = $this->post($route, [
            'RelayState'   => $targetRoute,
            'SAMLResponse' => '',
        ]);
        $resp->assertRedirect($targetRoute);

        self::assertAuthenticatedAs($this->superadmin());
    }

    public function test_it_logs_out_saml_user_from_saml_request()
    {
        $this->setUpMocks();
        \Artisan::call(RegisterSamlUserMapping::class, [
            'cpmUserId' => $this->superadmin()->id,
            'idp'       => 'testing',
            'idpUserId' => 'testUser',
        ]);

        $route = route('saml2_acs', [
            'idpName' => 'testing',
        ]);
        $this->post($route, [
            'SAMLResponse' => '',
        ]);
        self::assertAuthenticatedAs($this->superadmin());

        $route = route('saml2_sls', [
            'idpName' => 'testing',
        ]);
        $this->get($route, [
            'SAMLResponse' => '',
        ]);
        event(new Saml2LogoutEvent('testing'));
        self::assertFalse(self::isAuthenticated());
    }

    public function test_it_maps_cpm_user_with_saml_user()
    {
        self::assertNotNull($this->superadmin());
        \Artisan::call(RegisterSamlUserMapping::class, [
            'cpmUserId' => $this->superadmin()->id,
            'idp'       => 'testing',
            'idpUserId' => 'testUser',
        ]);
        $samlUser = SamlUser::where('idp', '=', 'testing')
            ->where('idp_user_id', '=', 'testUser')
            ->first();
        self::assertNotNull($samlUser);
        self:self::assertEquals($this->superadmin()->id, $samlUser->cpm_user_id);
    }

    private function setUpMocks()
    {
        $repo = Mockery::mock(Saml2Auth::class);
        $this->instance(Saml2Auth::class, $repo);

        $repo->shouldReceive('acs')
            ->andReturnNull();

        $repo->shouldReceive('sls')
            ->andReturnNull();

        $oneLoginAuth = Mockery::mock(OneLogin_Saml2_Auth::class);
        $oneLoginAuth->shouldReceive('getAttributesWithFriendlyName')
            ->andReturn(['uid' => 'testUser']);
        $repo->shouldReceive('getSaml2User')
            ->andReturn(new Saml2User($oneLoginAuth));

        $repo->shouldReceive('getIntendedUrl')
            ->andReturnNull();
    }
}
