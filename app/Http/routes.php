<?php
// unprotected
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

Route::get('/', 'WelcomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);


/****************************/
//     LARAVEL SITE ROUTES
/****************************/

Route::group(['middleware' => 'auth'], function ()
{
	Route::get('home', 'HomeController@index');

	Route::resource('apikeys', 'ApiKeyController', [
		'only' => [ 'index', 'destroy', 'store' ],
	]);

	Route::get('rules', 'RulesController@index');
	Route::get('rules/create', ['uses' =>'RulesController@create', 'as'=>'rulesCreate']);
	Route::get('rules/{id}', ['uses' =>'RulesController@show', 'as'=>'rulesShow']);
	Route::get('rules/{id}/edit', ['uses' =>'RulesController@edit', 'as'=>'rulesEdit']);
	Route::post('rules/{id}/edit', ['uses' =>'RulesController@update', 'as'=>'rulesUpdate']);
	Route::get('rulesmatches', ['uses' =>'RulesController@showMatches', 'as'=>'rulesMatches']);

	Route::get('pagetimer', 'PageTimerController@index');
	Route::get('pagetimer/create', ['uses' =>'PageTimerController@create', 'as'=>'pageTimerCreate']);
	Route::get('pagetimer/{id}', ['uses' =>'PageTimerController@show', 'as'=>'pageTimerShow']);
	Route::get('pagetimer/{id}/edit', ['uses' =>'PageTimerController@edit', 'as'=>'pageTimerEdit']);

	Route::get('activities', 'ActivityController@index');
	Route::get('activities/create', ['uses' =>'ActivityController@create', 'as'=>'activitiesCreate']);
	Route::get('activities/{id}', ['uses' =>'ActivityController@show', 'as'=>'activitiesShow']);
	Route::get('activities/{id}/edit', ['uses' =>'ActivityController@edit', 'as'=>'activitiesEdit']);

	Route::get('wpusers', 'WpUserController@index');
	Route::get('wpusers/create', ['uses' =>'WpUserController@create', 'as'=>'usersCreate']);
	Route::get('wpusers/{id}', ['uses' =>'WpUserController@show', 'as'=>'usersShow']);
	Route::get('wpusers/{id}/edit', ['uses' =>'WpUserController@edit', 'as'=>'usersEdit']);
	Route::post('wpusers/{id}/edit', ['uses' =>'WpUserController@update', 'as'=>'usersUpdate']);
	Route::get('wpusers/{id}/careplan', ['uses' =>'CareplanController@show', 'as'=>'usersCareplan']);
	Route::get('wpusers/{id}/msgcenter', ['uses' =>'WpUserController@showMsgCenter', 'as'=>'usersMsgCenter']);

    Route::group(['namespace' => 'Redox'], function ()
    {
        Route::get('redox', [
            'uses' => 'AppVerificationController@getVerificationRequest'
        ]);

        Route::post('redox', [
            'uses' => 'AppVerification@postRedox'
        ]);

        Route::group(['middleware' => 'getRedoxAccessToken'], function()
        {
            Route::get('testRedoxx', 'PostToRedoxController@index');
        });
    });

    /*
     * Third Party Apis Config
     */
    Route::group(['prefix' => 'third-party-api-settings'], function ()
    {
        Route::resource('redox-engine', 'Redox\ConfigController', [
            'except' => [ 'index', 'destroy', 'show' ]
        ]);

        Route::resource('qliqsoft', 'qliqSOFT\ConfigController', [
            'except' => [ 'index', 'destroy', 'show' ]
        ]);


    });
    

});


/***********************/
//     API ROUTES
/***********************/

// pagetimer
Route::group(['middleware' => 'cors'], function(){
	//Route::get('pagetimer', 'PageTimerController@store');
	Route::post('api/v2.1/pagetimer', 'PageTimerController@store');
});

/*
 * // NOTES:
		// http://www.toptal.com/web/cookie-free-authentication-with-json-web-tokens-an-example-in-laravel-and-angularjs
		// https://github.com/tymondesigns/jwt-auth/issues/79
		// http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#rfc.section.3.1
		// https://stormpath.com/blog/where-to-store-your-jwts-cookies-vs-html5-web-storage/
		// http://pythonhackers.com/p/tymondesigns/jwt-auth

		// fix for authorization: bearer header .htaccess:
		// http://stackoverflow.com/questions/20853604/laravel-get-request-headers

		// formatting
		// http://www.sitepoint.com/build-rest-resources-laravel/
		// http://www.vinaysahni.com/best-practices-for-a-pragmatic-restful-api#restful

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

	// observations
    Route::post('comment', 'CommentController@store');
    Route::post('observation', 'ObservationController@store');
    Route::get('careplan', 'CareplanController@show');

	// locations
	Route::get('locations', 'LocationController@index');

	// blogs (programs)
	Route::get('programs', 'WpBlogController@index');
});



/**********************************/
//  WP SPECIFIC API SITE ROUTES
/**********************************/

Route::group(['prefix' => 'wp/api/v2.1', 'middleware' => 'authApiCall'], function()
{
	// activities
	Route::resource('activities', 'ActivityController');

	Route::post('activities/update', 'ActivityController@update');

	Route::resource('activities.meta', 'ActivityMetaController');

	// reports
    Route::get('reports/pagetimer', 'ReportsController@pageTimerReports');
	Route::resource('reports', 'ReportsController');

	// locations
	Route::get('locations', 'LocationController@index');
});

// legacy api routes @todo migrate and remove these
Route::group(['middleware' => 'authApiCall'], function()
{

	Route::resource('rules', 'RulesController');

	Route::resource('pagetimer', 'PageTimerController');

	//Route::resource('reports', 'ReportsController');

	Route::resource('locations', 'LocationController');

	Route::resource('locations/show', 'LocationController');

	//Route::resource('activities', 'ActivityController');

    //Route::post('activities/update', 'ActivityController@update');

	//Route::resource('activities.meta', 'ActivityMetaController');

	//Route::resource('wpusers', 'WpUserController');

	// Route::resource('wpusers.meta', 'WpUserMetaController');

	Route::resource('rulesucp', 'CPRulesUCPController');

	Route::resource('rulespcp', 'CPRulesPCPController');

	Route::resource('rulesitem', 'CPRulesItemController');

	Route::resource('rulesitem.meta', 'CPRulesItemMetaController');

	Route::resource('observation', 'ObservationController');

	Route::resource('observation.meta', 'ObservationMetaController');
});