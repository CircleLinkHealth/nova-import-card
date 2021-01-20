<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::get('saml2/logout-success', [
    'uses' => 'Saml2Controller@showLogoutSuccess',
    'as'   => 'saml.logout.success',
])->middleware('saml');

Route::get('saml2/error', [
    'uses' => 'Saml2Controller@showError',
    'as'   => 'saml.error',
])->middleware('saml');

Route::get('saml2/not-auth', [
    'uses' => 'Saml2Controller@showNotAuth',
    'as'   => 'saml.not.auth',
])->middleware('saml');
