<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);

Route::group(['middleware' => 'auth'], function ()
{
	Route::get('home', 'HomeController@index');
});

Route::group(['middleware' => 'authApiCall'], function()
{
	Route::resource('locations', 'LocationController');

	Route::resource('activities', 'ActivityController');

	Route::resource('activities.meta', 'ActivityMetaController');

	Route::resource('wpusers', 'WpUserController');

	Route::resource('wpusers.meta', 'WpUserMetaController');

	Route::resource('rulesucp', 'RulesUCPController');

	Route::resource('rulespcp', 'RulesPCPController');

	Route::resource('rulesitem', 'RulesItemController');

	Route::resource('rulesitem.meta', 'RulesItemMetaController');


});