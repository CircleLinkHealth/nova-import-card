<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Http\Controllers;

use Aacotroneo\Saml2\Saml2Auth;
use Illuminate\Http\Request;

class Saml2Controller extends \Aacotroneo\Saml2\Http\Controllers\Saml2Controller
{
    public function acs(Saml2Auth $saml2Auth, $idpName)
    {
        return parent::acs($saml2Auth, $idpName);
    }

    public function login(Saml2Auth $saml2Auth)
    {
        parent::login($saml2Auth);
    }

    public function logout(Saml2Auth $saml2Auth, Request $request)
    {
        parent::logout($saml2Auth, $request);
    }

    public function metadata(Saml2Auth $saml2Auth)
    {
        return parent::metadata($saml2Auth);
    }

    public function showError(Request $request)
    {
        return view('samlsp::error');
    }

    public function showLogoutSuccess(Request $request)
    {
        return view('samlsp::logout-success');
    }

    public function sls(Saml2Auth $saml2Auth, $idpName)
    {
        return parent::sls($saml2Auth, $idpName);
    }
}
