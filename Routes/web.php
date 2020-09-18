<?php

Route::get('login', 'Auth\LoginController@showLoginForm', ['as' => 'login']);
Route::get('auth/login', 'Auth\LoginController@showLoginForm', ['as' => 'auth.login']);
Route::post('login', 'Auth\LoginController@login');
Route::post('auth/login', 'Auth\LoginController@login');
Route::post('browser-check', [
    'uses' => 'Auth\LoginController@storeBrowserCompatibilityCheckPreference',
    'as'   => 'store.browser.compatibility.check.preference',
]);

Route::group([
    'prefix'     => 'auth',
    'middleware' => ['web'],
], function () {
    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
    Route::get('password/confirm', 'Auth\ConfirmPasswordController@showConfirmForm')->name('password.confirm');
    Route::post('password/confirm', 'Auth\ConfirmPasswordController@confirm');
    
    Route::get('logout', [
        'uses' => 'Auth\LoginController@logout',
        'as'   => 'user.logout',
    ]);
    Route::get('inactivity-logout', [
        'uses' => 'Auth\LoginController@inactivityLogout',
        'as'   => 'user.inactivity-logout',
    ]);
});