<?php
/*
 * NO AUTHENTICATION NEEDED FOR THESE ROUTES
 */

//CCD Parser Demo Route
Route::get('ccd-parser-demo', 'WelcomeController@index');

Route::get('/ccd/{id}', function($id)
{
	$ccd = \App\XmlCCD::whereUserId($id)->first()->ccd;

	$template = View::make('CCDViewer.bb-ccd-viewer', compact('ccd'))->render();

	return View::make('CCDViewer.viewer', compact('template'));
});

/**
 * UPLOAD CCD ROUTES
 * @todo How do we protect those? auth middleware?
 */
Route::get('upload-raw-ccds', 'CCDUploadController@create');
Route::post('upload-raw-ccds', 'CCDUploadController@uploadRawFiles');
Route::post('upload-parsed-ccds', 'CCDUploadController@storeParsedFiles');

Route::group(['middleware' => 'auth.ccd.import'], function (){
	Route::post('{id}/import-ccds', 'CCDUploadController@create');
});

//Test route @todo remove after testing
Route::get('/reports/progress/{id}', function($id){
	$report = new \App\Services\ReportsService();
	return $report->careplan($id);
});

Route::get('test/form/{blogId}','WpUserController@quickAddForm');
Route::post('test/form/dump','WpUserController@storeQuickAddAPI');

Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

Route::get('/', 'WelcomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
Route::get('login', ['uses' => 'Auth\AuthController@getLogin', 'as' => 'login']);

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
	// PATIENTS (/patients/
	/****************************/
	Route::group(['prefix' => 'patients/', 'middleware' => 'programCheck'], function () {
		Route::get('dashboard', ['uses' => 'Patient\PatientController@showDashboard', 'as' => 'patients.dashboard']);
		Route::get('alerts', ['uses' => 'Patient\PatientController@showPatientAlerts', 'as' => 'patients.alerts']);
		Route::get('careplan/demographics', ['uses' => 'Patient\PatientCareplanController@showPatientDemographics', 'as' => 'patients.demographics.show']);
		Route::post('careplan/demographics', ['uses' => 'Patient\PatientCareplanController@storePatientDemographics', 'as' => 'patients.demographics.store']);
	});

	/****************************/
	// PATIENT (/patient/patientId)
	/****************************/
	Route::group(['prefix' => 'patient/{patientId}', 'middleware' => 'programCheck'], function () {

		// base
		//Route::get('/', ['uses' => 'Patient\PatientController@showSelectProgram', 'as' => 'patient.selectprogram']);
		Route::get('summary', ['uses' => 'Patient\PatientController@showPatientSummary', 'as' => 'patient.summary']);
		Route::get('alerts', ['uses' => 'Patient\PatientController@showPatientAlerts', 'as' => 'patient.alerts']);
		Route::get('input/observation', ['uses' => 'Patient\PatientController@showPatientObservationCreate', 'as' => 'patient.observation.create']);
		Route::post('input/observation/create', ['uses' => 'ObservationController@store', 'as' => 'patient.observation.store']);

		// careplan
		Route::group(['prefix' => 'careplan'], function () {
			// careplan user
			Route::get('demographics', ['uses' => 'Patient\PatientCareplanController@showPatientDemographics', 'as' => 'patient.demographics.show']);
			Route::post('demographics', ['uses' => 'Patient\PatientCareplanController@storePatientDemographics', 'as' => 'patient.demographics.store']);
			// careplan team
			Route::get('team', ['uses' => 'Patient\PatientCareplanController@showPatientCareteam', 'as' => 'patient.careteam.show']);
			Route::post('team', ['uses' => 'Patient\PatientCareplanController@storePatientCareteam', 'as' => 'patient.careteam.store']);
			// careplan sections
			Route::get('sections', ['uses' => 'Patient\PatientCareplanController@showPatientCareplan', 'as' => 'patient.careplan.show']);
			Route::post('sections', ['uses' => 'Patient\PatientCareplanController@storePatientCareplan', 'as' => 'patient.careplan.store']);
			// print
			Route::get('sections/print', ['uses' => 'Patient\PatientCareplanController@showPatientCareplanPrint', 'as' => 'patient.careplan.print']);
		});

		// notes
		Route::group(['prefix' => 'notes'], function () {
			Route::get('', ['uses' => 'Patient\PatientController@showPatientNotes', 'as' => 'patient.notes']);
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
	Route::get('users', ['uses' =>'WpUserController@index', 'as'=>'users.index']);
	Route::post('users', ['uses' =>'WpUserController@store', 'as'=>'users.store']);
	Route::get('users/create', ['uses' =>'WpUserController@create', 'as'=>'users.create']);
	Route::get('users/{id}/edit', ['uses' =>'WpUserController@edit', 'as'=>'users.edit']);
	Route::post('users/{id}/edit', ['uses' =>'WpUserController@update', 'as'=>'users.update']);
	Route::get('users/createQuickPatient/{blogId}', ['uses' =>'WpUserController@createQuickPatient', 'as'=>'users.createQuickPatient']);
	Route::post('users/createQuickPatient/', ['uses' =>'WpUserController@storeQuickPatient', 'as'=>'users.storeQuickPatient']);
	Route::get('users/{id}/careplan', ['uses' =>'CareplanController@show', 'as'=>'users.careplan']);
	Route::get('users/{id}/msgcenter', ['uses' =>'WpUserController@showMsgCenter', 'as'=>'users.msgCenter']);
	Route::post('users/{id}/msgcenter', ['uses' =>'WpUserController@showMsgCenter', 'as'=>'users.msgCenterUpdate']);

	// locations
	Route::resource('locations', 'LocationController');

	/****************************/
	// ADMIN (/admin)
	/****************************/
	Entrust::routeNeedsRole('admin/*', array('administrator','developer','care-center'), Redirect::to( URL::route('login') ), false);
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

		// questionSets
		Route::resource('questionSets', 'Admin\CPRQuestionSetController');
		Route::post('questionSets', ['uses' =>'Admin\CPRQuestionSetController@index', 'as'=>'admin.questionSets']);
		Route::post('questionSets/{id}/edit', ['uses' =>'Admin\CPRQuestionSetController@update', 'as'=>'admin.questionSets.update']);
		Route::get('questionSets/{id}/destroy', ['uses' =>'Admin\CPRQuestionSetController@destroy', 'as'=>'admin.questionSets.destroy']);

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
	Route::controller('password', 'Auth\PasswordController');

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
	Route::get('reports/UIprogress', 'ReportsController@UIprogress');
	Route::resource('reports', 'ReportsController');
	Route::get('reports/progress', 'ReportsController@progress');

	// locations
	Route::get('locations', 'LocationController@index');

	// observations
	Route::post('observation', 'ObservationController@store');

	// users
	Route::get('user/quickadd', 'WpUserController@showQuickAddAPI');
	Route::post('user/quickadd', 'WpUserController@storeQuickAddAPI');
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
