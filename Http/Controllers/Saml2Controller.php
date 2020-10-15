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
        if (config('samlsp.dump_acs_saml_request', false)
            && app()->environment('staging')
            && isset($_POST['SAMLResponse'])) {
            if (app()->bound('sentry')) {
                app('sentry')->captureMessage($_POST['SAMLResponse']);
            }
        }

        return parent::acs($saml2Auth, $idpName);
    }

    public function login(Saml2Auth $saml2Auth)
    {
        parent::login($saml2Auth);
    }

    public function logout(Saml2Auth $saml2Auth, Request $request)
    {
        if (app()->bound('sentry')) {
            app('sentry')->captureMessage('logout called'.json_encode(request()->all()));
        }

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

    public function showNotAuth(Request $request)
    {
        return view('samlsp::not-auth');
    }

    public function sls(Saml2Auth $saml2Auth, $idpName)
    {
        if (app()->bound('sentry')) {
            app('sentry')->captureMessage('sls called'.json_encode(request()->all()));
        }

        if (config('samlsp.dump_sls_saml_request', false)
            && app()->environment('staging')
            && isset($_GET['SAMLResponse'])) {
            if (app()->bound('sentry')) {
                app('sentry')->captureMessage($_GET['SAMLResponse']);
            }
        }

        return parent::sls($saml2Auth, $idpName);
    }
}
