<?php
/*
 * NO AUTHENTICATION NEEDED FOR THESE ROUTES
 */
//CCD Parser Demo Route
Route::get('ccd-parser-demo', 'CCDParserDemoController@index');

/**
 * UPLOAD CCD ROUTES
 * @todo How do we protect those? auth middleware?
 */
Route::get('upload-raw-ccds', 'CCDUploadController@create');
Route::post('upload-raw-ccds', 'CCDUploadController@uploadRawFiles');
Route::post('upload-duplicate-raw-ccds', 'CCDUploadController@uploadDuplicateRawFiles');
Route::post('upload-parsed-ccds', 'CCDUploadController@storeParsedFiles');

Route::group(['middleware' => 'auth.ccd.import'], function (){
	Route::post('{id}/import-ccds', 'CCDUploadController@create');
});

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
    /**
     * LOGGER
     */
    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

    /****************************
	 * CCD STUFF
	 ****************************/
	Route::get('ccd/show/{userId}', 'CCDViewer\CCDViewerController@showByUserId');
	Route::post('ccd', ['uses' => 'CCDViewer\CCDViewerController@showUploadedCcd', 'as' => 'ccd-viewer.post']);

	Route::post('ccd/old-viewer', ['uses' => 'CCDViewer\CCDViewerController@viewSource', 'as' => 'ccd.old.viewer']);
	Route::get('ccd/old-viewer', 'CCDViewer\CCDViewerController@create');
	Route::post('ccd-old', ['uses' => 'CCDViewer\CCDViewerController@oldViewer', 'as' => 'ccd-old-viewer.post']);


    /****************************
     * VUE CCD VIEWER
     ****************************/
    Route::get('vue', function () {
        return view('CCDViewer.new-vuer');
    });
    Route::get('getVueVar/{ccdId}', function ($ccdId) {
        //Amazing Charts Sample
//        return App\XmlCCD::find(434)->ccd;
//        return response('123', 400);
        return App\XmlCCD::find($ccdId)->ccd;

        //BB Sample
        //	return App\XmlCCD::find(430)->ccd;
    });

	/****************************/
	// PROVIDER UI (/manage-patients, /reports, ect)
	/****************************/

	// **** PATIENTS (/manage-patients/
	Route::group(['prefix' => 'manage-patients/', 'middleware' => 'patientProgramSecurity'], function () {
		Route::get('dashboard', ['uses' => 'Patient\PatientController@showDashboard', 'as' => 'patients.dashboard']);
		Route::get('listing', ['uses' => 'Patient\PatientController@showPatientListing', 'as' => 'patients.listing']);
		Route::get('select', ['uses' => 'Patient\PatientController@showPatientSelect', 'as' => 'patients.select']);
		Route::get('alerts', ['uses' => 'Patient\PatientController@showPatientAlerts', 'as' => 'patients.alerts']);
		Route::get('careplan/demographics', ['uses' => 'Patient\PatientCareplanController@showPatientDemographics', 'as' => 'patients.demographics.show']);
		Route::post('careplan/demographics', ['uses' => 'Patient\PatientCareplanController@storePatientDemographics', 'as' => 'patients.demographics.store']);
	});

	// **** PATIENTS (/manage-patients/{patientId}/)
	Route::group(['prefix' => 'manage-patients/{patientId}', 'middleware' => 'patientProgramSecurity'], function () {

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
			Route::get('create', ['uses' => 'NotesController@create', 'as' => 'patient.note.create']);
			Route::post('store', ['uses' => 'NotesController@store', 'as' => 'patient.note.store']);
			Route::get('', ['uses' => 'NotesController@index', 'as' => 'patient.note.index']);
			Route::get('view/{noteId}', ['uses' => 'NotesController@show', 'as' => 'patient.note.show']);
		});

		// activities
		Route::group(['prefix' => 'activities'], function () {
			Route::get('create', ['uses' => 'ActivityController@create', 'as' => 'patient.activity.create']);
			Route::post('store', ['uses' => 'ActivityController@store', 'as' => 'patient.activity.store']);
			Route::get('', ['uses' => 'ActivityController@providerUIIndex', 'as' => 'patient.activity.providerUIIndex']);
		});

		Route::get('progress', ['uses' => 'ReportsController@index', 'as' => 'patient.reports.progress']);
		Route::get('u20', ['uses' => 'ReportsController@u20', 'as' => 'patient.reports.u20']);
		Route::get('billing', ['uses' => 'ReportsController@billing', 'as' => 'patient.reports.billing']);
	});

	/****************************/
	// ADMIN (/admin)
	/****************************/
	Route::group(['prefix' => 'admin'], function () {

		$prefix = 'admin/'; // admin prefix
		Entrust::routeNeedsPermission($prefix.'*', 'admin-access');

		// dashboard
		Route::get('', ['uses' =>'Admin\DashboardController@index', 'as'=>'admin.dashboard']);

		// activities
		Entrust::routeNeedsPermission($prefix.'activities*', 'activities-view');
		Route::resource('activities', 'ActivityController');
		Route::get('activities/create', ['uses' =>'ActivityController@create', 'as'=>'admin.activities.create']);
		Route::get('activities/{id}', ['uses' =>'ActivityController@show', 'as'=>'admin.activities.show']);
		Route::get('activities/{id}/edit', ['uses' =>'ActivityController@edit', 'as'=>'admin.activities.edit']);

		// pagetimer
		Entrust::routeNeedsPermission($prefix.'pagetimer*', 'activities-pagetimer-view');
		Route::resource('pagetimer', 'PageTimerController');
		Route::get('pagetimer/create', ['uses' =>'PageTimerController@create', 'as'=>'admin.pagetimer.create']);
		Route::get('pagetimer/{id}', ['uses' =>'PageTimerController@show', 'as'=>'admin.pagetimer.show']);
		Route::get('pagetimer/{id}/edit', ['uses' =>'PageTimerController@edit', 'as'=>'admin.pagetimer.edit']);

		// wpusers
		Entrust::routeNeedsPermission($prefix.'users*', 'users-view-all');
		Route::get('users', ['uses' =>'UserController@index', 'as'=>'admin.users.index']);
		Route::post('users', ['uses' =>'UserController@store', 'as'=>'admin.users.store']);
		Route::get('users/create', ['uses' =>'UserController@create', 'as'=>'admin.users.create']);
		Route::get('users/{id}/edit', ['uses' =>'UserController@edit', 'as'=>'admin.users.edit']);
		Route::post('users/{id}/edit', ['uses' =>'UserController@update', 'as'=>'admin.users.update']);
		Route::get('users/createQuickPatient/{blogId}', ['uses' =>'UserController@createQuickPatient', 'as'=>'admin.users.createQuickPatient']);
		Route::post('users/createQuickPatient/', ['uses' =>'UserController@storeQuickPatient', 'as'=>'admin.users.storeQuickPatient']);
		Route::get('users/{id}/careplan', ['uses' =>'CareplanController@show', 'as'=>'admin.users.careplan']);
		Route::get('users/{id}/msgcenter', ['uses' =>'UserController@showMsgCenter', 'as'=>'admin.users.msgCenter']);
		Route::post('users/{id}/msgcenter', ['uses' =>'UserController@showMsgCenter', 'as'=>'admin.users.msgCenterUpdate']);

		// rules
		Entrust::routeNeedsPermission($prefix.'rules*', 'rules-engine-view');
		Route::resource('rules', 'RulesController');
		Route::get('rules/create', ['uses' =>'RulesController@create', 'as'=>'admin.rules.create']);
		Route::post('rules/store', ['uses' =>'RulesController@store', 'as'=>'admin.rules.store']);
		Route::get('rules/{id}', ['uses' =>'RulesController@show', 'as'=>'admin.rules.show']);
		Route::get('rules/{id}/edit', ['uses' =>'RulesController@edit', 'as'=>'admin.rules.edit']);
		Route::post('rules/{id}/edit', ['uses' =>'RulesController@update', 'as'=>'admin.rules.update']);
		Route::get('rulesmatches', ['uses' =>'RulesController@showMatches', 'as'=>'admin.rules.matches']);

		// roles
		Entrust::routeNeedsPermission($prefix.'roles*', 'roles-view');
		Entrust::routeNeedsPermission($prefix.'roles/*/*', 'roles-manage');
		Route::resource('roles', 'Admin\RoleController');
		Route::post('roles/{id}/edit', ['uses' =>'Admin\RoleController@update', 'as'=>'admin.roles.update']);

		// permissions
		Entrust::routeNeedsPermission($prefix.'permissions*', 'roles-permissions-view');
		Entrust::routeNeedsPermission($prefix.'permissions/*/*', 'roles-permissions-manage');
		Route::resource('permissions', 'Admin\PermissionController');
		Route::post('permissions/{id}/edit', ['uses' =>'Admin\PermissionController@update', 'as'=>'admin.permissions.update']);

		// questions
		Entrust::routeNeedsPermission($prefix.'questions*', 'programs-manage');
		Route::resource('questions', 'Admin\CPRQuestionController');
		Route::post('questions/{id}/edit', ['uses' =>'Admin\CPRQuestionController@update', 'as'=>'admin.questions.update']);
		Route::get('questions/{id}/destroy', ['uses' =>'Admin\CPRQuestionController@destroy', 'as'=>'admin.questions.destroy']);

		// questionSets
		Entrust::routeNeedsPermission($prefix.'questionSets*', 'programs-manage');
		Route::resource('questionSets', 'Admin\CPRQuestionSetController');
		Route::post('questionSets', ['uses' =>'Admin\CPRQuestionSetController@index', 'as'=>'admin.questionSets']);
		Route::post('questionSets/{id}/edit', ['uses' =>'Admin\CPRQuestionSetController@update', 'as'=>'admin.questionSets.update']);
		Route::get('questionSets/{id}/destroy', ['uses' =>'Admin\CPRQuestionSetController@destroy', 'as'=>'admin.questionSets.destroy']);

		// items
		Entrust::routeNeedsPermission($prefix.'items*', 'programs-manage');
		Route::resource('items', 'Admin\CPRItemController');
		Route::post('items/{id}/edit', ['uses' =>'Admin\CPRItemController@update', 'as'=>'admin.items.update']);
		Route::get('items/{id}/destroy', ['uses' =>'Admin\CPRItemController@destroy', 'as'=>'admin.items.destroy']);

		// ucp
		Entrust::routeNeedsPermission($prefix.'ucp*', 'programs-manage');
		Route::resource('ucp', 'Admin\CPRUCPController');
		Route::post('ucp/{id}/edit', ['uses' =>'Admin\CPRUCPController@update', 'as'=>'admin.ucp.update']);
		Route::get('ucp/{id}/destroy', ['uses' =>'Admin\CPRUCPController@destroy', 'as'=>'admin.ucp.destroy']);

		// observations
		Entrust::routeNeedsPermission($prefix.'observations*', 'observations-view');
		Entrust::routeNeedsPermission($prefix.'observations/edit', 'observations-edit');
		Entrust::routeNeedsPermission($prefix.'observations/create', 'observations-create');
		Route::resource('observations', 'Admin\ObservationController');
		Route::post('observations', ['uses' =>'Admin\ObservationController@index', 'as'=>'admin.observations']);
		Route::post('observations/{id}/edit', ['uses' =>'Admin\ObservationController@update', 'as'=>'admin.observations.update']);
		Route::get('observations/{id}/destroy', ['uses' =>'Admin\ObservationController@destroy', 'as'=>'admin.observations.destroy']);

		// comments
		Entrust::routeNeedsPermission($prefix.'comments*', 'observations-view');
		Entrust::routeNeedsPermission($prefix.'comments/edit', 'observations-edit');
		Entrust::routeNeedsPermission($prefix.'comments/create', 'observations-create');
		Route::resource('comments', 'Admin\CommentController');
		Route::post('comments/{id}/edit', ['uses' =>'Admin\CommentController@update', 'as'=>'admin.comments.update']);
		Route::get('comments/{id}/destroy', ['uses' =>'Admin\CommentController@destroy', 'as'=>'admin.comments.destroy']);

		// programs
		Entrust::routeNeedsPermission($prefix.'programs*', 'programs-view');
		Route::resource('programs', 'Admin\WpBlogController');
		Route::get('programs', ['uses' =>'Admin\WpBlogController@index', 'as'=>'admin.programs']);
		Route::get('programs/create', ['uses' =>'Admin\WpBlogController@create', 'as'=>'admin.programs.create']);
		Route::post('programs/create', ['uses' =>'Admin\WpBlogController@store', 'as'=>'admin.programs.store']);
		Route::get('programs/{id}', ['uses' =>'Admin\WpBlogController@show', 'as'=>'admin.programs.show']);
		Route::get('programs/{id}/edit', ['uses' =>'Admin\WpBlogController@edit', 'as'=>'admin.programs.edit']);
		Route::post('programs/{id}/edit', ['uses' =>'Admin\WpBlogController@update', 'as'=>'admin.programs.update']);
		Route::get('programs/{id}/questions', ['uses' =>'Admin\WpBlogController@showQuestions', 'as'=>'admin.programs.questions']);

		// locations
		Entrust::routeNeedsPermission($prefix.'locations*', 'programs-view');
		Route::resource('locations', 'LocationController');

		// apikeys
		Entrust::routeNeedsPermission($prefix.'apikeys*', 'apikeys-view');
		Route::resource('apikeys', 'Admin\ApiKeyController', [
			'only' => [ 'index', 'destroy', 'store' ],
		]);
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
	Route::post('user', 'UserController@index');
	Route::get('user', 'UserController@index');

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
