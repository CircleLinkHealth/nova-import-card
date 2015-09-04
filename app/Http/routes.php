<?php
/*
 * NO AUTHENTICATION NEEDED FOR THESE ROUTES
 */

//EMAIL TEST ROUTE
//Route::get('/email', function () {
//	$data = [
//		'title'=>'Email'
//	];
//	Mail::send('emails.newnote', $data, function($message) {
//		$message->from('no-reply@careplanmanager.com', 'CircleLink Health');
//		$message->to('philiplawlor@gmail.com')->subject('You have a new note!!');
//	});
//	return Redirect::to('/');
//});

Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

Route::get('/', 'WelcomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);

Route::group(['namespace' => 'Redox'], function ()
{
    Route::get('redox', [
        'uses' => 'AppVerificationController@getVerificationRequest'
    ]);

    Route::group(['middleware' => 'getRedoxAccessToken'], function()
    {
        //@todo: this is not an actual route, it was made for testing
        Route::get('testRedoxx', 'PostToRedoxController@index');
    });
});

/****************************/
//    AUTH ROUTES
/****************************/

Route::group(['middleware' => 'auth'], function ()
{
	Route::get('home', 'HomeController@index');

	Route::resource('apikeys', 'ApiKeyController', [
		'only' => [ 'index', 'destroy', 'store' ],
	]);

	Route::get('rules', 'RulesController@index');
	Route::get('rules/create', ['uses' =>'RulesController@create', 'as'=>'rulesCreate']);
	Route::post('rules/store', ['uses' =>'RulesController@store', 'as'=>'rulesStore']);
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
	Route::post('wpusers/create', ['uses' =>'WpUserController@store', 'as'=>'usersStore']);
	Route::get('wpusers/{id}', ['uses' =>'WpUserController@show', 'as'=>'usersShow']);
	Route::get('wpusers/{id}/edit', ['uses' =>'WpUserController@edit', 'as'=>'usersEdit']);
	Route::post('wpusers/{id}/edit', ['uses' =>'WpUserController@update', 'as'=>'usersUpdate']);
	Route::get('wpusers/{id}/careplan', ['uses' =>'CareplanController@show', 'as'=>'usersCareplan']);
	Route::get('wpusers/{id}/msgcenter', ['uses' =>'WpUserController@showMsgCenter', 'as'=>'usersMsgCenter']);
	Route::post('wpusers/{id}/msgcenter', ['uses' =>'WpUserController@showMsgCenter', 'as'=>'usersMsgCenterUpdate']);

	Route::get('programs', ['uses' =>'WpBlogController@index', 'as'=>'programsIndex']);
	Route::get('programs/{id}', ['uses' =>'WpBlogController@show', 'as'=>'programsShow']);
	Route::get('programs/{id}/edit', ['uses' =>'WpBlogController@edit', 'as'=>'programsEdit']);
	Route::post('programs/{id}/edit', ['uses' =>'WpBlogController@update', 'as'=>'programsUpdate']);
	Route::get('programs/{id}/questions', ['uses' =>'WpBlogController@showQuestions', 'as'=>'programsQuestionsShow']);

	/*
     * Admin
     */
	Route::group(['prefix' => 'admin'], function ()
	{
		Route::get('roles', 'Admin\RoleController@index');
		Route::get('roles/create', ['uses' =>'Admin\RoleController@create', 'as'=>'rolesCreate']);
		Route::post('roles/create', ['uses' =>'Admin\RoleController@store', 'as'=>'rolesStore']);
		Route::get('roles/{id}', ['uses' =>'Admin\RoleController@show', 'as'=>'rolesShow']);
		Route::get('roles/{id}/edit', ['uses' =>'Admin\RoleController@edit', 'as'=>'rolesEdit']);
		Route::post('roles/{id}/edit', ['uses' =>'Admin\RoleController@update', 'as'=>'rolesUpdate']);
		Route::get('roles/{id}/careplan', ['uses' =>'Admin\RoleController@show', 'as'=>'rolesCareplan']);

		Route::get('permissions', 'Admin\PermissionController@index');
		Route::get('permissions/create', ['uses' =>'Admin\PermissionController@create', 'as'=>'permissionsCreate']);
		Route::post('permissions/create', ['uses' =>'Admin\PermissionController@store', 'as'=>'permissionsStore']);
		Route::get('permissions/{id}', ['uses' =>'Admin\PermissionController@show', 'as'=>'permissionsShow']);
		Route::get('permissions/{id}/edit', ['uses' =>'Admin\PermissionController@edit', 'as'=>'permissionsEdit']);
		Route::post('permissions/{id}/edit', ['uses' =>'Admin\PermissionController@update', 'as'=>'permissionsUpdate']);
		Route::get('permissions/{id}/careplan', ['uses' =>'Admin\PermissionController@show', 'as'=>'permissionsCareplan']);
	});

    /*
     * Third Party Apis Config Pages
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
    Route::get('reports/progress', 'ReportsController@progress');
    Route::get('reports/careplan', 'ReportsController@careplan');

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

	// observations
	Route::post('observation', 'ObservationController@store');
});


/**********************************/
//  CRON ROUTES
/**********************************/
Route::group(['prefix' => 'cron'], function()
{
	Route::get('/scheduler/{id}', function ($id) {
		$msgScheduler = new \App\Services\MsgScheduler();
		$msgScheduler->index($id);
	});
});





// legacy api routes @todo migrate and remove these
Route::group(['middleware' => 'authApiCall'], function()
{

	//Route::resource('rules', 'RulesController');

	//Route::resource('pagetimer', 'PageTimerController');

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