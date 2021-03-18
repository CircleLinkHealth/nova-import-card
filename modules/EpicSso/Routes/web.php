<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::get('epic-sso/launch', [
    'uses' => 'EpicSsoController@launch',
    'as' => 'epic.sso.launch'
]);
Route::get('epic-sso/code', [
    'uses' => 'EpicSsoController@code',
    'as' => 'epic.sso.code'
]);
