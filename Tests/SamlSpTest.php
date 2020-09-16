<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Tests;

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SamlSp\Console\RegisterSamlUserMapping;
use CircleLinkHealth\SamlSp\Entities\SamlUser;
use Tests\CustomerTestCase;

class SamlSpTest extends CustomerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_authenticates_saml_user_from_saml_request()
    {
        self::assertTrue(false);
    }

    public function test_it_authenticates_saml_user_from_saml_request_and_redirects_to_target_url()
    {
        self::assertTrue(false);
    }

    public function test_it_logs_out_saml_user_from_saml_request()
    {
        self::assertTrue(false);
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
}
