<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\CpmAdmin\Http\Controllers\EhrController;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;

class EhrControllerTest extends CustomerTestCase
{
    public function test_get_ehrs()
    {
        $this->be($this->superadmin());

        $response = $this->get('/ehrs');
        $response->assertStatus(200);
        $ehrs = $response->json();
        self::assertNotEmpty($ehrs);
    }

    public function test_get_ehrs_sso_only()
    {
        $this->be($this->superadmin());

        $response = $this->get('/ehrs?onlySso=1');
        $response->assertStatus(200);

        $ehrs = $response->json();
        self::assertNotEmpty($ehrs);
        self::assertEquals(
            EhrController::SSO_INTEGRATIONS,
            collect($ehrs)->map(fn ($ehr) => strtolower($ehr['name']))->toArray()
        );
    }
}
