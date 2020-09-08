<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use Tests\TestCase;

class SamlTest extends TestCase
{
    /**
     * 1. Login as admin through normal CPM login
     * 2. Go to PAM page
     * 3. Logout.
     */
    public function test_should_login_normally()
    {
        self::assertTrue(false);
    }

    /**
     * 1. Login as admin through SAML
     * 2. Go to PAM page
     * 3. Logout.
     */
    public function test_should_login_through_idp()
    {
        self::assertTrue(false);
    }

    public function test_that_saml_attributes_are_added()
    {
        self::assertTrue(false);
    }

    public function test_that_saml_metadata_returns_xml_with_entity_id()
    {
        self::assertTrue(false);
    }
}
