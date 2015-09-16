<?php
/*
 * NO AUTHENTICATION NEEDED FOR THESE ROUTES
 */


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

	Route::get('wpusers', ['uses' =>'WpUserController@index', 'as'=>'wpusers.index']);
	Route::post('wpusers', 'WpUserController@index');
	Route::get('wpusers/create', ['uses' =>'WpUserController@create', 'as'=>'usersCreate']);
	Route::post('wpusers/create', ['uses' =>'WpUserController@store', 'as'=>'usersStore']);
	Route::get('wpusers/{id}', ['uses' =>'WpUserController@show', 'as'=>'usersShow']);
	Route::get('wpusers/{id}/edit', ['uses' =>'WpUserController@edit', 'as'=>'usersEdit']);
	Route::post('wpusers/{id}/edit', ['uses' =>'WpUserController@update', 'as'=>'usersUpdate']);
	Route::get('wpusers/{id}/careplan', ['uses' =>'CareplanController@show', 'as'=>'usersCareplan']);
	Route::get('wpusers/{id}/msgcenter', ['uses' =>'WpUserController@showMsgCenter', 'as'=>'usersMsgCenter']);
	Route::post('wpusers/{id}/msgcenter', ['uses' =>'WpUserController@showMsgCenter', 'as'=>'usersMsgCenterUpdate']);

	Route::get('patient/{id}/summary', ['uses' =>'Patient\PatientController@showPatientSummary', 'as'=>'patient.summary']);
	Route::get('patient/{id}/careplan', ['uses' =>'Patient\PatientController@showPatientCareplan', 'as'=>'patient.careplan']);

	/*
     * Admin
     */
	Route::group(['prefix' => 'admin'], function ()
	{
		Route::get('roles', ['uses' =>'Admin\RoleController@index', 'as'=>'admin.roles']);
		Route::get('roles/create', ['uses' =>'Admin\RoleController@create', 'as'=>'admin.roles.create']);
		Route::post('roles/create', ['uses' =>'Admin\RoleController@store', 'as'=>'admin.roles.store']);
		Route::get('roles/{id}', ['uses' =>'Admin\RoleController@show', 'as'=>'admin.roles.show']);
		Route::get('roles/{id}/edit', ['uses' =>'Admin\RoleController@edit', 'as'=>'admin.roles.edit']);
		Route::post('roles/{id}/edit', ['uses' =>'Admin\RoleController@update', 'as'=>'admin.roles.update']);
		Route::get('roles/{id}/careplan', ['uses' =>'Admin\RoleController@show', 'as'=>'admin.roles.careplan']);

		Route::get('permissions', ['uses' =>'Admin\PermissionController@index', 'as'=>'admin.permissions']);
		Route::get('permissions/create', ['uses' =>'Admin\PermissionController@create', 'as'=>'admin.permissions.create']);
		Route::post('permissions/create', ['uses' =>'Admin\PermissionController@store', 'as'=>'admin.permissions.store']);
		Route::get('permissions/{id}', ['uses' =>'Admin\PermissionController@show', 'as'=>'admin.permissions.show']);
		Route::get('permissions/{id}/edit', ['uses' =>'Admin\PermissionController@edit', 'as'=>'admin.permissions.edit']);
		Route::post('permissions/{id}/edit', ['uses' =>'Admin\PermissionController@update', 'as'=>'admin.permissions.update']);
		Route::get('permissions/{id}/careplan', ['uses' =>'Admin\PermissionController@show', 'as'=>'admin.permissions.careplan']);

		Route::get('questions', ['uses' =>'Admin\CPRQuestionController@index', 'as'=>'admin.questions']);
		Route::get('questions/create', ['uses' =>'Admin\CPRQuestionController@create', 'as'=>'admin.questions.create']);
		Route::post('questions/create', ['uses' =>'Admin\CPRQuestionController@store', 'as'=>'admin.questions.store']);
		Route::get('questions/{id}', ['uses' =>'Admin\CPRQuestionController@show', 'as'=>'admin.questions.show']);
		Route::get('questions/{id}/edit', ['uses' =>'Admin\CPRQuestionController@edit', 'as'=>'admin.questions.edit']);
		Route::post('questions/{id}/edit', ['uses' =>'Admin\CPRQuestionController@update', 'as'=>'admin.questions.update']);
		Route::get('questions/{id}/destroy', ['uses' =>'Admin\CPRQuestionController@destroy', 'as'=>'admin.questions.destroy']);

		Route::get('observations', ['uses' =>'Admin\ObservationController@index', 'as'=>'admin.observations']);
		Route::post('observations', ['uses' =>'Admin\ObservationController@index', 'as'=>'admin.observations']);
		Route::get('observations/create', ['uses' =>'Admin\ObservationController@create', 'as'=>'admin.observations.create']);
		Route::post('observations/create', ['uses' =>'Admin\ObservationController@store', 'as'=>'admin.observations.store']);
		Route::get('observations/{id}', ['uses' =>'Admin\ObservationController@show', 'as'=>'admin.observations.show']);
		Route::get('observations/{id}/edit', ['uses' =>'Admin\ObservationController@edit', 'as'=>'admin.observations.edit']);
		Route::post('observations/{id}/edit', ['uses' =>'Admin\ObservationController@update', 'as'=>'admin.observations.update']);
		Route::get('observations/{id}/destroy', ['uses' =>'Admin\ObservationController@destroy', 'as'=>'admin.observations.destroy']);

		Route::get('comments', ['uses' =>'Admin\CommentController@index', 'as'=>'admin.comments']);
		Route::get('comments/create', ['uses' =>'Admin\CommentController@create', 'as'=>'admin.comments.create']);
		Route::post('comments/create', ['uses' =>'Admin\CommentController@store', 'as'=>'admin.comments.store']);
		Route::get('comments/{id}', ['uses' =>'Admin\CommentController@show', 'as'=>'admin.comments.show']);
		Route::get('comments/{id}/edit', ['uses' =>'Admin\CommentController@edit', 'as'=>'admin.comments.edit']);
		Route::post('comments/{id}/edit', ['uses' =>'Admin\CommentController@update', 'as'=>'admin.comments.update']);
		Route::get('comments/{id}/destroy', ['uses' =>'Admin\CommentController@destroy', 'as'=>'admin.comments.destroy']);

		Route::get('programs', ['uses' =>'Admin\WpBlogController@index', 'as'=>'admin.programs']);
		Route::get('programs/create', ['uses' =>'Admin\WpBlogController@create', 'as'=>'admin.programs.create']);
		Route::post('programs/create', ['uses' =>'Admin\WpBlogController@store', 'as'=>'admin.programs.store']);
		Route::get('programs/{id}', ['uses' =>'Admin\WpBlogController@show', 'as'=>'admin.programs.show']);
		Route::get('programs/{id}/edit', ['uses' =>'Admin\WpBlogController@edit', 'as'=>'admin.programs.edit']);
		Route::post('programs/{id}/edit', ['uses' =>'Admin\WpBlogController@update', 'as'=>'admin.programs.update']);
		Route::get('programs/{id}/questions', ['uses' =>'Admin\WpBlogController@showQuestions', 'as'=>'admin.programs.questions']);
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
	Route::post('activities/sendNote','ActivityController@sendExistingNote');

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