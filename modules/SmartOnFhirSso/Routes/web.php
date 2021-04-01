<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::get('smart-on-fhir-sso/launch', [
    'uses' => 'SsoController@launch',
    'as' => 'smart.on.fhir.sso.launch'
]);
Route::get('smart-on-fhir-sso/epic-code', [
    'uses' => 'EpicSsoController@getAuthToken',
    'as' => 'smart.on.fhir.sso.epic.code'
]);
Route::get('smart-on-fhir-sso/smarthealthit-code', [
    'uses' => 'SmartHealthItSsoController@getAuthToken',
    'as' => 'smart.on.fhir.sso.smarthealthit.code'
]);
