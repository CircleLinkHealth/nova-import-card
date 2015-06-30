<?php
use App\User;
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

	Route::resource('apikeys', 'ApiKeyController', [
		'only' => [ 'index', 'destroy', 'store' ],
	]);
});


/***********************/
//     API ROUTES
/***********************/

/*
 * // NOTES:
		// http://www.toptal.com/web/cookie-free-authentication-with-json-web-tokens-an-example-in-laravel-and-angularjs
		// https://github.com/tymondesigns/jwt-auth/issues/79
		// http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#rfc.section.3.1
		// https://stormpath.com/blog/where-to-store-your-jwts-cookies-vs-html5-web-storage/

		// fix for authorization: bearer header .htaccess:
		// http://stackoverflow.com/questions/20853604/laravel-get-request-headers

		// formatting
		// http://www.sitepoint.com/build-rest-resources-laravel/

		// debug comment tag: kgallodebug
 */
// JWTauth Login
Route::post('api/v2.1/login', 'AuthorizationController@login');

// JWTauth api routes
Route::group(['before' => 'jwt-auth', 'prefix' => 'api/v2.1', 'middleware' => 'authApiCall'], function()
{
	// return token data, initial test
	Route::post('tokentest', 'AuthorizationController@tokentest');

	// return data on logged in user

	Route::post('user', 'WpUserController@index');
	Route::get('user', 'WpUserController@index');
    Route::post('comment', 'CommentController@store');
    Route::post('observation', 'ObservationController@store');

});

// legacy api routes @todo migrate and remove these
Route::group(['middleware' => 'authApiCall'], function()
{
	Route::resource('reports', 'ReportsController');

	Route::resource('locations', 'LocationController');

	Route::resource('locations/show', 'LocationController');

	Route::resource('activities', 'ActivityController');

	Route::resource('activities.meta', 'ActivityMetaController');

	Route::resource('wpusers', 'WpUserController');

	Route::resource('wpusers.meta', 'WpUserMetaController');

	Route::resource('rulesucp', 'RulesUCPController');

	Route::resource('rulespcp', 'RulesPCPController');

	Route::resource('rulesitem', 'RulesItemController');

	Route::resource('rulesitem.meta', 'RulesItemMetaController');

	Route::resource('observation', 'ObservationController');

	Route::resource('observation.meta', 'ObservationMetaController');
});