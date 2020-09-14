<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::get('saml2/error', [
    'uses' => 'Saml2Controller@showError',
    'as'   => 'saml.error',
])->middleware('web');
