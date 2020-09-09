<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\User;
use PHPUnit\Util\Xml;
use Tests\TestCase;

class SamlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        User::whereEmail('admin@example.org')->update([
            'skip_browser_checks' => true,
        ]);
    }

    /**
     * TODO: this should be a DUSK test.
     *
     * 1. Login as admin through SAML
     * 2. Go to PAM page
     * 3. Logout.
     */
    public function test_should_add_saml_request_input_in_form()
    {
        $encodedReq = 'fVFBbsIwEPxK5DuJSWgBiyClRVWRQEUk7aG3xWyIpcROvU7b59cJINEL0l6smfHMzi4ImroVWecqvcevDskFv02tSQxAyjqrhQFSJDQ0SMJJkWfbjYhDLlprnJGmZjeS%2BwogQuuU0Sx4MVbi4JuyEmpCFqxXKYNxmQAey4cEH2NI%2BOTI5zgpDyCniRxDUk5lPJtJ7OlEHa41OdAuZTGP%2BYjP%2FRR8Jvwk808WrPxCSkNvmbLKuVZEkWybUNZV6DwW1eakfJrsGuzZaOoatDnabyXxfb8568gLqe0loYKGKvMjgTCUpolAEguC3aWMJ6WPSp%2Fu93A4k0i8FsVutHvLC%2F9D8IGWhqCewpaLvk4xLGmX62ybXzwX0S1wfv2%2F4PIP';
        $url        = 'http://cpm.clh.test/login?SAMLRequest='.$encodedReq;
        $resp       = $this->get($url);
        $resp->assertOk();
        $resp->assertViewIs('auth.login');
        $content = $resp->getContent();
        self::assertStringContainsString('<input type="hidden" id="SAMLRequest" name="SAMLRequest" value="', $content);
    }

    /**
     * 1. Login as admin through normal CPM login
     * 2. Go to PAM page
     * 3. Logout.
     */
    public function test_should_login_normally()
    {
        $this->post(route('login'), [
            'email'    => 'admin@example.org',
            'password' => 'hello',
        ])->assertRedirect(route('home'));
    }

    public function test_should_login_through_idp()
    {
        $encodedReq = 'fVFBbsIwEPxK5DuJSWgBiyClRVWRQEUk7aG3xWyIpcROvU7b59cJINEL0l6smfHMzi4ImroVWecqvcevDskFv02tSQxAyjqrhQFSJDQ0SMJJkWfbjYhDLlprnJGmZjeS%2BwogQuuU0Sx4MVbi4JuyEmpCFqxXKYNxmQAey4cEH2NI%2BOTI5zgpDyCniRxDUk5lPJtJ7OlEHa41OdAuZTGP%2BYjP%2FRR8Jvwk808WrPxCSkNvmbLKuVZEkWybUNZV6DwW1eakfJrsGuzZaOoatDnabyXxfb8568gLqe0loYKGKvMjgTCUpolAEguC3aWMJ6WPSp%2Fu93A4k0i8FsVutHvLC%2F9D8IGWhqCewpaLvk4xLGmX62ybXzwX0S1wfv2%2F4PIP';
        $resp       = $this->post(route('login'), [
            'SAMLRequest' => urldecode($encodedReq),
            'email'       => 'admin@example.org',
            'password'    => 'hello',
        ])->assertOk();
        $content = $resp->getContent();
        self::assertStringContainsString('<input type="hidden" name="SAMLResponse" value="', $content);
    }

    public function test_that_saml_metadata_returns_xml_with_entity_id()
    {
        $url = config('samlidp.issuer_uri');
        self::assertNotNull($url);

        $resp = $this->get($url);
        $resp->assertOk();
        $resp->assertHeader('Content-Type', 'application/xml');
        $body  = $resp->getContent();
        $xml   = Xml::load($body);
        $found = false;
        foreach ($xml->firstChild->attributes as $attribute) {
            if ('entityID' === $attribute->name) {
                $found = true;
            }
        }
        self::assertTrue($found);
    }
}
