<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::get('smart-on-fhir-sso/launch', [
    'uses' => 'SsoController@launch',
    'as' => 'smart.on.fhir.sso.launch'
])->middleware(config('smartonfhirsso.routes_middleware'));
Route::get('smart-on-fhir-sso/error', [
    'uses' => 'SsoController@showError',
    'as'   => 'smart.on.fhir.sso.error',
])->middleware(config('smartonfhirsso.routes_middleware'));
Route::get('smart-on-fhir-sso/not-auth', [
    'uses' => 'SsoController@showNotAuth',
    'as'   => 'smart.on.fhir.sso.not.auth',
])->middleware(config('smartonfhirsso.routes_middleware'));

Route::get('smart-on-fhir-sso/epic-code', [
    'uses' => 'EpicSsoController@getAuthToken',
    'as' => 'smart.on.fhir.sso.epic.code'
])->middleware(config('smartonfhirsso.routes_middleware'));

Route::get('smart-on-fhir-sso/smarthealthit-code', [
    'uses' => 'SmartHealthItSsoController@getAuthToken',
    'as' => 'smart.on.fhir.sso.smarthealthit.code'
])->middleware(config('smartonfhirsso.routes_middleware'));
