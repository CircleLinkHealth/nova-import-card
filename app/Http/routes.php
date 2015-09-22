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

// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');

/****************************/
/****************************/
//    REDOX
/****************************/
/****************************/
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
/****************************/
//    AUTH ROUTES
/****************************/
/****************************/
Route::group(['middleware' => 'auth'], function ()
{
	/****************************/
	// HOME BASE
	/****************************/
	Route::get('home', 'HomeController@index');

	/****************************/
	// PATIENT (/patient/programId)
	/****************************/
	Route::group(['prefix' => 'patient/{programId}'], function () {

		// base
		Route::get('summary/{id}', ['uses' => 'Patient\PatientController@showPatientSummary', 'as' => 'patient.summary']);
		Route::get('alerts/{id?}', ['uses' => 'Patient\PatientController@showPatientAlerts', 'as' => 'patient.alerts']);
		Route::get('input/observation/{id}', ['uses' => 'Patient\PatientController@showPatientObservationCreate', 'as' => 'patient.observation.create']);
		Route::post('input/observation/create', ['uses' => 'ObservationController@store', 'as' => 'patient.observation.store']);

		// careplan
		Route::group(['prefix' => 'careplan'], function () {
			Route::get('{id}', ['uses' => 'Patient\PatientCareplanController@showPatientCareplan', 'as' => 'patient.careplan']);
			Route::post('save', ['uses' => 'Patient\PatientCareplanController@savePatientCareplan', 'as' => 'patient.careplan.save']);
			Route::get('{id}/print', ['uses' => 'Patient\PatientCareplanController@showPatientCareplanPrint', 'as' => 'patient.careplan.print']);
		});

		// notes
		Route::group(['prefix' => 'notes'], function () {
			Route::get('{id}', ['uses' => 'Patient\PatientController@showPatientNotes', 'as' => 'patient.notes']);
		});
	});

	/****************************/
	// ADMIN (without /admin)
	/****************************/
	// apikeys
	Route::resource('apikeys', 'ApiKeyController', [
		'only' => [ 'index', 'destroy', 'store' ],
	]);

	// rules
	Route::get('rules', 'RulesController@index');
	Route::get('rules/create', ['uses' =>'RulesController@create', 'as'=>'rulesCreate']);
	Route::post('rules/store', ['uses' =>'RulesController@store', 'as'=>'rulesStore']);
	Route::get('rules/{id}', ['uses' =>'RulesController@show', 'as'=>'rulesShow']);
	Route::get('rules/{id}/edit', ['uses' =>'RulesController@edit', 'as'=>'rulesEdit']);
	Route::post('rules/{id}/edit', ['uses' =>'RulesController@update', 'as'=>'rulesUpdate']);
	Route::get('rulesmatches', ['uses' =>'RulesController@showMatches', 'as'=>'rulesMatches']);

	// pagetimer
	Route::get('pagetimer', 'PageTimerController@index');
	Route::get('pagetimer/create', ['uses' =>'PageTimerController@create', 'as'=>'pageTimerCreate']);
	Route::get('pagetimer/{id}', ['uses' =>'PageTimerController@show', 'as'=>'pageTimerShow']);
	Route::get('pagetimer/{id}/edit', ['uses' =>'PageTimerController@edit', 'as'=>'pageTimerEdit']);

	// activities
	Route::get('activities', 'ActivityController@index');
	Route::get('activities/create', ['uses' =>'ActivityController@create', 'as'=>'activitiesCreate']);
	Route::get('activities/{id}', ['uses' =>'ActivityController@show', 'as'=>'activitiesShow']);
	Route::get('activities/{id}/edit', ['uses' =>'ActivityController@edit', 'as'=>'activitiesEdit']);

	// wpusers
	Route::get('wpusers', ['uses' =>'WpUserController@index', 'as'=>'users.index']);
	Route::post('wpusers', ['uses' =>'WpUserController@index', 'as'=>'users.index']);
	Route::get('wpusers/create', ['uses' =>'WpUserController@create', 'as'=>'users.create']);
	Route::post('wpusers/create', ['uses' =>'WpUserController@store', 'as'=>'users.store']);
	Route::get('wpusers/{id}', ['uses' =>'WpUserController@show', 'as'=>'users.show']);
	Route::get('wpusers/{id}/edit', ['uses' =>'WpUserController@edit', 'as'=>'users.edit']);
	Route::post('wpusers/{id}/edit', ['uses' =>'WpUserController@update', 'as'=>'users.update']);
	Route::get('wpusers/{id}/careplan', ['uses' =>'CareplanController@show', 'as'=>'users.careplan']);
	Route::get('wpusers/{id}/msgcenter', ['uses' =>'WpUserController@showMsgCenter', 'as'=>'users.msgCenter']);
	Route::post('wpusers/{id}/msgcenter', ['uses' =>'WpUserController@showMsgCenter', 'as'=>'users.msgCenterUpdate']);

	// locations
	Route::resource('locations', 'LocationController');

	/****************************/
	// ADMIN (/admin)
	/****************************/
	Entrust::routeNeedsRole('admin/*', array('administrator','developer','care-center'), null, false);
	Route::group(['prefix' => 'admin'], function () {

		// home
		Route::get('home', ['uses' =>'HomeController@index', 'as'=>'admin.home']);

		// roles
		Route::resource('roles', 'Admin\RoleController');
		Route::post('roles/{id}/edit', ['uses' =>'Admin\RoleController@update', 'as'=>'admin.roles.update']);

		// permissions
		Route::resource('permissions', 'Admin\PermissionController');
		Route::post('permissions/{id}/edit', ['uses' =>'Admin\PermissionController@update', 'as'=>'admin.permissions.update']);

		// questions
		Route::resource('questions', 'Admin\CPRQuestionController');
		Route::post('questions/{id}/edit', ['uses' =>'Admin\CPRQuestionController@update', 'as'=>'admin.questions.update']);
		Route::get('questions/{id}/destroy', ['uses' =>'Admin\CPRQuestionController@destroy', 'as'=>'admin.questions.destroy']);

		// items
		Route::resource('items', 'Admin\CPRItemController');
		Route::post('items/{id}/edit', ['uses' =>'Admin\CPRItemController@update', 'as'=>'admin.items.update']);
		Route::get('items/{id}/destroy', ['uses' =>'Admin\CPRItemController@destroy', 'as'=>'admin.items.destroy']);

		// ucp
		Route::resource('ucp', 'Admin\CPRUCPController');
		Route::post('ucp/{id}/edit', ['uses' =>'Admin\CPRUCPController@update', 'as'=>'admin.ucp.update']);
		Route::get('ucp/{id}/destroy', ['uses' =>'Admin\CPRUCPController@destroy', 'as'=>'admin.ucp.destroy']);

		// observations
		Route::resource('observations', 'Admin\ObservationController');
		Route::post('observations', ['uses' =>'Admin\ObservationController@index', 'as'=>'admin.observations']);
		Route::post('observations/{id}/edit', ['uses' =>'Admin\ObservationController@update', 'as'=>'admin.observations.update']);
		Route::get('observations/{id}/destroy', ['uses' =>'Admin\ObservationController@destroy', 'as'=>'admin.observations.destroy']);

		// comments
		Route::resource('comments', 'Admin\CommentController');
		Route::post('comments/{id}/edit', ['uses' =>'Admin\CommentController@update', 'as'=>'admin.comments.update']);
		Route::get('comments/{id}/destroy', ['uses' =>'Admin\CommentController@destroy', 'as'=>'admin.comments.destroy']);

		// programs
		Route::resource('programs', 'Admin\WpBlogController');
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
/***********************/
//     API ROUTES
/***********************/
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

	// Password reset link request routes...
	Route::post('password/email', 'Auth\PasswordController@postEmail'); // @todo still needs to be modified to return json api response

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



/*
// legacy api routes @todo migrate and remove these
Route::group(['middleware' => 'authApiCall'], function()
{

	Route::resource('locations', 'LocationController');

	Route::resource('locations/show', 'LocationController');

	Route::resource('rulesucp', 'CPRulesUCPController');

	Route::resource('rulespcp', 'CPRulesPCPController');

	Route::resource('rulesitem', 'CPRulesItemController');

	Route::resource('rulesitem.meta', 'CPRulesItemMetaController');

	Route::resource('observation', 'ObservationController');

	Route::resource('observation.meta', 'ObservationMetaController');
});
*/
