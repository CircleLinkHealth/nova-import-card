<?php

//Add some time so that it won't mess with the values of the previous test
//$startTime = Carbon\Carbon::now()->copy()->addSeconds(10);
//$endTime = $startTime->copy()->addSeconds(60);
//
//$create = new Illuminate\Support\Collection([
//    [
//        $startTime->copy()->subMinutes(2),
//        $endTime->copy()->addMinutes(2),
//        $startTime->copy()->subMinutes(2)->diffInSeconds($endTime->copy()->addMinutes(2)),
//        'non ccm',
//    ],
//    [
//        $startTime,
//        $endTime,
//        $startTime->diffInSeconds($endTime),
//        'non ccm',
//    ],
//]);
//
//foreach ($create as $c) {
//    $request = new Illuminate\Http\Request();
//
//    $request->merge([
//        'patientId'        => 285,
//        'providerId'       => 357,
//        'totalTime'        => $c[2] * 1000,
//        'programId'        => 9,
//        'startTime'        => $c[0]->toDateTimeString(),
//        'testEndTime'      => $c[1]->toDateTimeString(),
//        'urlFull'          => 'www.url.com',
//        'urlShort'         => 'url.com',
//        'ipAddr'           => '1.1.1.1',
//        'activity'         => 'Patient Overview Review',
//        'title'            => $c[3],
//        'testing'          => true,
//        'redirectLocation' => '',
//    ]);
//
//    (new App\Http\Controllers\PageTimerController($request, new App\Services\TimeTracking\Service))->store($request);
//}

if (app()->environment() != 'production') {


    Route::get('rohan', function () {

        return (new \App\PatientContactWindow())->getEarliestWindowForPatientFromDate(\App\PatientInfo::find(3198),
            \Carbon\Carbon::today());

//        $result = Program::find(21)->enrollmentByProgram(Carbon\Carbon::parse('2016-09-01 00:00:00'),
//            Carbon\Carbon::parse('2016-09-30 23:59:59'));
//
//        return ['thing' => $result];

    });
}

//Algo test routes.

Route::group(['prefix' => 'algo'], function () {

    Route::get('family', function () {

        if (app()->environment() == 'production') {

            return 'Sorry, this cannot be run on the production environment.';

        }

        return (new \App\Services\Calls\SchedulerService())->syncFamilialCalls();

    });

    Route::get('cleaner', function () {

        if (app()->environment() == 'production') {

            return 'Sorry, this cannot be run on the production environment.';

        }

        return (new \App\Services\Calls\SchedulerService())->removeScheduledCallsForWithdrawnAndPausedPatients();

    });

    Route::get('tuner', function () {

        if (app()->environment() == 'production') {

            return 'Sorry, this cannot be run on the production environment.';

        }

        return (new \App\Services\Calls\SchedulerService())->tuneScheduledCallsWithUpdatedCCMTime();

    });

    Route::get('rescheduler', function () {

        if (app()->environment() == 'production') {

            return 'Sorry, this cannot be run on the production environment.';

        }

        return (new \App\Algorithms\Calls\ReschedulerHandler())->handle();

    });
});

Route::group(['prefix' => 'api/v1.0'], function () {
    //Should change this to a GET to make this RESTful
    Route::post('oauth/access_token', 'CcdApi\Aprima\AuthController@getAccessToken');

    Route::group(['middleware' => 'aprima.ccdapi.auth.adapter'], function () {
        //Should make this plural
        Route::post('ccd', 'CcdApi\Aprima\CcdApiController@uploadCcd');
        Route::get('reports', 'CcdApi\Aprima\CcdApiController@reports');

        Route::get('notes', 'CcdApi\Aprima\CcdApiController@notes');

        //Let's make things RESTful from here onwards
        Route::get('ccm-times', 'CcdApi\Aprima\CcdApiController@getCcmTime');
    });
});

Route::controller('ajax', 'UserController');

Route::get('careplan/{id}', [
    'uses' => 'Admin\CarePlanController@carePlan',
    'as'   => 'careplan',
]);
Route::get('careplan/{id}/section/{sectionId}', [
    'uses' => 'Admin\CarePlanController@carePlanSection',
    'as'   => 'careplan.section',
]);

/*
 * NO AUTHENTICATION NEEDED FOR THESE ROUTES
 */
Route::post('account/login', 'Patient\PatientController@patientAjaxSearch');

Route::get('/', 'WelcomeController@index');
Route::get('home', 'WelcomeController@index');

Route::controllers([
    'auth'     => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);
Route::get('login', [
    'uses' => 'Auth\AuthController@getLogin',
    'as'   => 'login',
]);
Route::get('auth/logout', [
    'uses' => 'Auth\AuthController@getLogout',
    'as'   => 'logout',
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
Route::group(['namespace' => 'Redox'], function () {
    Route::get('redox', [
        'uses' => 'AppVerificationController@getVerificationRequest',
    ]);

    Route::group(['middleware' => 'getRedoxAccessToken'], function () {
        //@todo: this is not an actual route, it was made for testing
        Route::get('testRedoxx', 'PostToRedoxController@index');
    });
});

/****************************/
/****************************/
//    AUTH ROUTES
/****************************/
/****************************/
Route::group(['middleware' => 'auth'], function () {

    Route::resource('settings/email', 'EmailSettingsController');

    Route::get('/CCDModels/Items/MedicationListItem', 'CCDModels\Items\MedicationListItemController@index');
    Route::post('/CCDModels/Items/MedicationListItem/store', 'CCDModels\Items\MedicationListItemController@store');
    Route::post('/CCDModels/Items/MedicationListItem/update',
        'CCDModels\Items\MedicationListItemController@update');
    Route::post('/CCDModels/Items/MedicationListItem/destroy',
        'CCDModels\Items\MedicationListItemController@destroy');

    Route::get('/CCDModels/Items/ProblemsItem', 'CCDModels\Items\ProblemsItemController@index');
    Route::post('/CCDModels/Items/ProblemsItem/store', 'CCDModels\Items\ProblemsItemController@store');
    Route::post('/CCDModels/Items/ProblemsItem/update', 'CCDModels\Items\ProblemsItemController@update');
    Route::post('/CCDModels/Items/ProblemsItem/destroy', 'CCDModels\Items\ProblemsItemController@destroy');

    Route::get('/CCDModels/Items/AllergiesItem', 'CCDModels\Items\AllergiesItemController@index');
    Route::post('/CCDModels/Items/AllergiesItem/store', 'CCDModels\Items\AllergiesItemController@store');
    Route::post('/CCDModels/Items/AllergiesItem/update', 'CCDModels\Items\AllergiesItemController@update');
    Route::post('/CCDModels/Items/AllergiesItem/destroy', 'CCDModels\Items\AllergiesItemController@destroy');


    /****************************
     * CCD STUFF
     ****************************/
    Route::get('ccd/show/{userId}', [
        'uses' => 'CCDViewer\CCDViewerController@showByUserId',
        'as'   => 'get.CCDViewerController.showByUserId',
    ]);

    Route::post('ccd', [
        'uses' => 'CCDViewer\CCDViewerController@showUploadedCcd',
        'as'   => 'ccd-viewer.post',
    ]);

    Route::post('ccd/old-viewer', [
        'uses' => 'CCDViewer\CCDViewerController@viewSource',
        'as'   => 'ccd.old.viewer',
    ]);
    Route::get('ccd/old-viewer', 'CCDViewer\CCDViewerController@create');
    Route::post('ccd-old', [
        'uses' => 'CCDViewer\CCDViewerController@oldViewer',
        'as'   => 'ccd-old-viewer.post',
    ]);


    /****************************
     * VUE CCD VIEWER
     ****************************/
    Route::get('vue', function () {
        return view('CCDViewer.new-vuer');
    });


    /**
     * CCD Importer Routes
     */
    Route::group([
        'middleware' => [
            'permission:ccd-import',
        ],
        'prefix'     => 'ccd-importer',
    ], function () {

        Route::get('create', [
            'uses' => 'CCDUploadController@create',
            'as'   => 'import.ccd',
        ]);

        Route::post('qaimport', 'CCDUploadController@uploadRawFiles');
        Route::get('qaimport', [
            'uses' => 'CCDUploadController@index',
            'as'   => 'view.files.ready.to.import',
        ]);

        Route::post('import', 'CCDImportController@import');

        Route::get('uploaded-ccd-items/{ccdaId}/edit', 'QAImportedController@edit');

        Route::post('demographics', 'EditImportedCcda\DemographicsImportsController@store');
    });

    //CCD Parser Demo Route
    Route::get('ccd-parser-demo', 'CCDParserDemoController@index');


    /****************************/
    // PROVIDER UI (/manage-patients, /reports, ect)
    /****************************/

    // **** PATIENTS (/manage-patients/
    Route::group([
        'prefix'     => 'manage-patients/',
        'middleware' => ['patientProgramSecurity'],
    ], function () {
        Route::get('dashboard', [
            'uses' => 'Patient\PatientController@showDashboard',
            'as'   => 'patients.dashboard',
        ]);
        Route::get('listing', [
            'uses' => 'Patient\PatientController@showPatientListing',
            'as'   => 'patients.listing',
        ]);
        Route::get('careplan-print-multi', [
            'uses' => 'Patient\PatientCareplanController@printMultiCareplan',
            'as'   => 'patients.careplan.multi',
        ]);
        Route::get('careplan-print-list', [
            'uses' => 'Patient\PatientCareplanController@index',
            'as'   => 'patients.careplan.printlist',
        ]);
        Route::post('select', [
            'uses' => 'Patient\PatientController@processPatientSelect',
            'as'   => 'patients.select.process',
        ]);
        Route::get('search', [
            'uses' => 'Patient\PatientController@patientAjaxSearch',
            'as'   => 'patients.search',
        ]);
        Route::get('queryPatient', [
            'uses' => 'Patient\PatientController@queryPatient',
            'as'   => 'patients.query',
        ]);
        Route::get('alerts', [
            'uses' => 'Patient\PatientController@showPatientAlerts',
            'as'   => 'patients.alerts',
        ]);
        Route::get('careplan/demographics', [
            'uses' => 'Patient\PatientCareplanController@showPatientDemographics',
            'as'   => 'patients.demographics.show',
        ]);
        Route::post('careplan/demographics', [
            'uses' => 'Patient\PatientCareplanController@storePatientDemographics',
            'as'   => 'patients.demographics.store',
        ]);
        Route::get('u20', [
            'uses' => 'ReportsController@u20',
            'as'   => 'patient.reports.u20',
        ]);
        Route::get('billing', [
            'uses' => 'ReportsController@billing',
            'as'   => 'patient.reports.billing',
        ]);
        Route::get('provider-notes', [
            'uses' => 'NotesController@listing',
            'as'   => 'patient.note.listing',
        ]);
        // nurse call list
        Route::group(['prefix' => 'patient-call-list'], function () {
            Route::get('', [
                'uses' => 'PatientCallListController@index',
                'as'   => 'patientCallList.index',
            ]);
        });
    });

    // **** PATIENTS (/manage-patients/{patientId}/)
    Route::group([
        'prefix'     => 'manage-patients/{patientId}',
        'middleware' => 'patientProgramSecurity',
    ], function () {

        // base
        //Route::get('/', ['uses' => 'Patient\PatientController@showSelectProgram', 'as' => 'patient.selectprogram']);
        Route::get('summary', [
            'uses' => 'Patient\PatientController@showPatientSummary',
            'as'   => 'patient.summary',
        ]);
        Route::get('summary-biochart', [
            'uses' => 'ReportsController@biometricsCharts',
            'as'   => 'patient.charts',
        ]);
        Route::get('alerts', [
            'uses' => 'Patient\PatientController@showPatientAlerts',
            'as'   => 'patient.alerts',
        ]);
        Route::get('input/observation', [
            'uses' => 'Patient\PatientController@showPatientObservationCreate',
            'as'   => 'patient.observation.create',
        ]);
        Route::get('view-careplan', [
            'uses' => 'ReportsController@viewPrintCareplan',
            'as'   => 'patient.careplan.print',
        ]);
        Route::post('input/observation/create', [
            'uses' => 'ObservationController@store',
            'as'   => 'patient.observation.store',
        ]);

        // careplan
        Route::group(['prefix' => 'careplan'], function () {
            // careplan user
            Route::get('demographics', [
                'uses' => 'Patient\PatientCareplanController@showPatientDemographics',
                'as'   => 'patient.demographics.show',
            ]);
            Route::post('demographics', [
                'uses' => 'Patient\PatientCareplanController@storePatientDemographics',
                'as'   => 'patient.demographics.store',
            ]);
            // careplan team
            Route::get('team', [
                'uses' => 'Patient\PatientCareplanController@showPatientCareteam',
                'as'   => 'patient.careteam.show',
            ]);
            Route::post('team', [
                'uses' => 'Patient\PatientCareplanController@storePatientCareteam',
                'as'   => 'patient.careteam.store',
            ]);
            // careplan sections
            Route::get('sections/{page}', [
                'uses' => 'Patient\PatientCareplanController@showPatientCareplan',
                'as'   => 'patient.careplan.show',
            ]);
            Route::post('sections', [
                'uses' => 'Patient\PatientCareplanController@storePatientCareplan',
                'as'   => 'patient.careplan.store',
            ]);
        });

        // notes
        Route::group(['prefix' => 'notes'], function () {
            Route::get('create', [
                'uses' => 'NotesController@create',
                'as'   => 'patient.note.create',
            ]);
            Route::post('store', [
                'uses' => 'NotesController@store',
                'as'   => 'patient.note.store',
            ]);
            Route::get('', [
                'uses' => 'NotesController@index',
                'as'   => 'patient.note.index',
            ]);
            Route::get('view/{noteId}', [
                'uses' => 'NotesController@show',
                'as'   => 'patient.note.view',
            ]);
            Route::post('send/{noteId}', [
                'uses' => 'NotesController@send',
                'as'   => 'patient.note.send',
            ]);
        });
        Route::get('progress', [
            'uses' => 'ReportsController@index',
            'as'   => 'patient.reports.progress',
        ]);

        // activities
        Route::group(['prefix' => 'activities'], function () {
            Route::get('create', [
                'uses' => 'ActivityController@create',
                'as'   => 'patient.activity.create',
            ]);
            Route::post('store', [
                'uses' => 'ActivityController@store',
                'as'   => 'patient.activity.store',
            ]);
            Route::get('view/{actId}', [
                'uses' => 'ActivityController@show',
                'as'   => 'patient.activity.view',
            ]);
            Route::get('', [
                'uses' => 'ActivityController@providerUIIndex',
                'as'   => 'patient.activity.providerUIIndex',
            ]);
        });

        //call scheduling
        Route::group(['prefix' => 'calls'], function () {
            Route::get('', [
                'uses' => 'CallController@index',
                'as'   => 'call.index',
            ]);
            Route::get('create', [
                'uses' => 'CallController@create',
                'as'   => 'call.create',
            ]);
            Route::post('schedule', [
                'uses' => 'CallController@schedule',
                'as'   => 'call.schedule',
            ]);
            Route::get('edit/{actId}', [
                'uses' => 'CallController@edit',
                'as'   => 'call.edit',
            ]);
        });
    });

    /****************************/
    // ADMIN (/admin)
    /****************************/
    Route::group([
        'middleware' => [
            'permission:admin-access',
        ],
        'prefix'     => 'admin',
    ], function () {

        /**
         * LOGGER
         */
        Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

        Route::get('download/{fileName}', [
            'uses' => 'DownloadController@file',
            'as'   => 'download',
        ])->where('filename', '[A-Za-z0-9\-\_\.]+');

        Route::get('nurses/windows', [
            'uses' => 'CareCenter\WorkScheduleController@getAllNurseSchedules',
            'as'   => 'get.admin.nurse.schedules',
        ]);

        Route::post('nurses/{id}/windows', [
            'uses' => 'CareCenter\WorkScheduleController@postAdminStoreWindow',
            'as'   => 'post.admin.store.nurse.schedules',
        ]);

        Route::patch('nurses/window/{id}', [
            'uses' => 'CareCenter\WorkScheduleController@patchAdminEditWindow',
            'as'   => 'patch.admin.edit.nurse.schedules',
        ]);

        Route::get('athena/check', 'CcdApi\Athena\AthenaApiController@getTodays');

        Route::post('calls/import', [
            'uses' => 'CallController@import',
            'as'   => 'post.CallController.import',
        ]);

        Route::get('families/create', [
            'uses' => 'FamilyController@create',
            'as'   => 'family.create',
        ]);

        Route::post('general-comments/import', [
            'uses' => 'Admin\UploadsController@postGeneralCommentsCsv',
            'as'   => 'post.GeneralCommentsCsv',
        ]);

        Route::get('calls/{patientId}', 'CallController@showCallsForPatient');


        Route::group([
            'prefix' => 'reports',
        ], function () {
            Route::post('monthly-billing', [
                'uses' => 'Admin\Reports\MonthlyBillingReportsController@makeMonthlyReport',
                'as'   => 'MonthlyBillingReportsController.makeMonthlyReport',
            ]);

            Route::get('sales/create', [
                'uses' => 'ReportsController@createSalesReport',
                'as'   => 'reports.sales.create',
            ]);

            Route::post('sales/make', [
                'uses' => 'ReportsController@makeSalesReport',
                'as'   => 'reports.sales.make',
            ]);

            Route::get('monthly-billing/create', [
                'uses' => 'Admin\Reports\MonthlyBillingReportsController@create',
                'as'   => 'MonthlyBillingReportsController.create',
            ]);

            Route::get('ethnicity', [
                'uses' => 'Admin\Reports\EthnicityReportController@getReport',
                'as'   => 'EthnicityReportController.getReport',
            ]);

            Route::get('call', [
                'uses' => 'Admin\Reports\CallReportController@exportxls',
                'as'   => 'CallReportController.exportxls',
            ]);

            Route::get('provider-usage', [
                'uses' => 'Admin\Reports\ProviderUsageReportController@index',
                'as'   => 'ProviderUsageReportController.index',
            ]);

            Route::get('provider-monthly-usage', [
                'uses' => 'Admin\Reports\ProviderMonthlyUsageReportController@index',
                'as'   => 'ProviderMonthlyUsageReportController.index',
            ]);

            Route::get('patient-conditions', [
                'uses' => 'Admin\Reports\PatientConditionsReportController@exportxls',
                'as'   => 'PatientConditionsReportController.getReport',
            ]);
        });

        //Algo Mocker
        Route::group(['prefix' => 'algo'], function () {
            Route::get('mock', [
                'uses' => 'AlgoController@createMock',
                'as'   => 'algo.mock.create',
            ]);

            Route::post('compute', [
                'uses' => 'AlgoController@computeMock',
                'as'   => 'algo.mock.compute',
            ]);
        });

        Route::get('emr-direct/check', function () {
            (new \App\Services\PhiMail\PhiMail())->sendReceive();
        });

        Route::get('dupes', function () {
            $results = DB::select(DB::raw("
                SELECT *
                FROM lv_activities
                WHERE performed_at != '0000-00-00 00:00:00'
                AND performed_at > '2016-04-30'
                /*AND duration != '0'*/
                AND provider_id != '1877'
                /*group by concat(performed_at, provider_id)
                having count(*) >= 2 */"));
            $a = 0;
            foreach ($results as $result) {
                echo $result->id .
                    ' - ' . $result->provider_id .
                    ' - ' . $result->performed_at .
                    '<br /><br />';
                $a++;
            }
            echo "TOTAL:" . $a;
            dd('done');
        });

        // excel reports
        Route::get('excelReportT1', [
            'uses' => 'ReportsController@excelReportT1',
            'as'   => 'excel.report.t1',
        ]);
        Route::get('excelReportT2', [
            'uses' => 'ReportsController@excelReportT2',
            'as'   => 'excel.report.t2',
        ]);
        Route::get('excelReportT3', [
            'uses' => 'ReportsController@excelReportT3',
            'as'   => 'excel.report.t3',
        ]);
        Route::get('excelReportT4', [
            'uses' => 'ReportsController@excelReportT4',
            'as'   => 'excel.report.t4',
        ]);

        // dashboard
        Route::get('', [
            'uses' => 'Admin\DashboardController@index',
            'as'   => 'admin.dashboard',
        ]);
        Route::get('testplan', [
            'uses' => 'Admin\DashboardController@testplan',
            'as'   => 'admin.testplan',
        ]);

        // impersonation
        Route::post('impersonate', [
            'uses' => 'ImpersonationController@postImpersonate',
            'as'   => 'post.impersonate',
        ]);

        // appConfig
        Route::group([
            'middleware' => [
                'permission:app-config-view',
            ],
        ], function () {
            Route::resource('appConfig', 'Admin\AppConfigController');
        });

        Route::group([
            'middleware' => [
                'permission:app-config-manage',
            ],
        ], function () {
            Route::post('appConfig/{id}/edit', [
                'uses' => 'Admin\AppConfigController@update',
                'as'   => 'admin.appConfig.update',
            ]);
            Route::get('appConfig/{id}/destroy', [
                'uses' => 'Admin\AppConfigController@destroy',
                'as'   => 'admin.appConfig.destroy',
            ]);
        });


        // activities
        Route::group([
            'middleware' => [
                'permission:activities-view',
            ],
        ], function () {
            Route::resource('activities', 'ActivityController');
            Route::get('activities/create', [
                'uses' => 'ActivityController@create',
                'as'   => 'admin.activities.create',
            ]);
            Route::get('activities/{id}', [
                'uses' => 'ActivityController@show',
                'as'   => 'admin.activities.show',
            ]);
            Route::get('activities/{id}/edit', [
                'uses' => 'ActivityController@edit',
                'as'   => 'admin.activities.edit',
            ]);
        });

        // pagetimer
        Route::group([
            'middleware' => [
                'permission:activities-pagetimer-view',
            ],
        ], function () {
            Route::resource('pagetimer', 'PageTimerController');
            Route::get('pagetimer/create', [
                'uses' => 'PageTimerController@create',
                'as'   => 'admin.pagetimer.create',
            ]);
            Route::get('pagetimer/{id}', [
                'uses' => 'PageTimerController@show',
                'as'   => 'admin.pagetimer.show',
            ]);
            Route::get('pagetimer/{id}/edit', [
                'uses' => 'PageTimerController@edit',
                'as'   => 'admin.pagetimer.edit',
            ]);
        });

        // users
        Route::group([
            'middleware' => [
                'permission:users-view-all',
            ],
        ], function () {
            Route::get('users', [
                'uses' => 'UserController@index',
                'as'   => 'admin.users.index',
            ]);
            Route::post('users', [
                'uses' => 'UserController@store',
                'as'   => 'admin.users.store',
            ]);
            Route::get('users/create', [
                'uses' => 'UserController@create',
                'as'   => 'admin.users.create',
            ]);
            Route::get('users/doAction', [
                'uses' => 'UserController@doAction',
                'as'   => 'admin.users.doAction',
            ]);
            Route::get('users/{id}/edit', [
                'uses' => 'UserController@edit',
                'as'   => 'admin.users.edit',
            ]);
            Route::get('users/{id}/destroy', [
                'uses' => 'UserController@destroy',
                'as'   => 'admin.users.destroy',
            ]);
            Route::post('users/{id}/edit', [
                'uses' => 'UserController@update',
                'as'   => 'admin.users.update',
            ]);
            Route::get('users/createQuickPatient/{blogId}', [
                'uses' => 'UserController@createQuickPatient',
                'as'   => 'admin.users.createQuickPatient',
            ]);
            Route::post('users/createQuickPatient/', [
                'uses' => 'UserController@storeQuickPatient',
                'as'   => 'admin.users.storeQuickPatient',
            ]);
            Route::get('users/{id}/careplan', [
                'uses' => 'CareplanController@show',
                'as'   => 'admin.users.careplan',
            ]);
            Route::get('users/{id}/msgcenter', [
                'uses' => 'UserController@showMsgCenter',
                'as'   => 'admin.users.msgCenter',
            ]);
            Route::post('users/{id}/msgcenter', [
                'uses' => 'UserController@showMsgCenter',
                'as'   => 'admin.users.msgCenterUpdate',
            ]);
            Route::get('users/patientCallManagement', [
                'uses' => 'Admin\PatientCallManagementController@index',
                'as'   => 'admin.patientCallManagement.index',
            ]);
            Route::get('users/patientCallManagement/{id}/edit', [
                'uses' => 'Admin\PatientCallManagementController@edit',
                'as'   => 'admin.patientCallManagement.edit',
            ]);
            Route::post('users/patientCallManagement/{id}/edit', [
                'uses' => 'Admin\PatientCallManagementController@update',
                'as'   => 'admin.patientCallManagement.update',
            ]);
        });

        // families
        Route::group([
            'middleware' => [
                'permission:users-view-all',
            ],
        ], function () {
            Route::get('families', [
                'uses' => 'FamilyController@index',
                'as'   => 'admin.families.index',
            ]);
            Route::post('families', [
                'uses' => 'FamilyController@store',
                'as'   => 'admin.families.store',
            ]);
            Route::get('families/create', [
                'uses' => 'FamilyController@create',
                'as'   => 'admin.families.create',
            ]);
            Route::get('families/{id}/edit', [
                'uses' => 'FamilyController@edit',
                'as'   => 'admin.families.edit',
            ]);
            Route::get('families/{id}/destroy', [
                'uses' => 'FamilyController@destroy',
                'as'   => 'admin.families.destroy',
            ]);
            Route::post('families/{id}/edit', [
                'uses' => 'FamilyController@update',
                'as'   => 'admin.families.update',
            ]);
        });

        // rules
        Route::group([
            'middleware' => [
                'permission:rules-engine-view',
            ],
        ], function () {
            Route::resource('rules', 'RulesController');
            Route::get('rules/create', [
                'uses' => 'RulesController@create',
                'as'   => 'admin.rules.create',
            ]);
            Route::post('rules/store', [
                'uses' => 'RulesController@store',
                'as'   => 'admin.rules.store',
            ]);
            Route::get('rules/{id}', [
                'uses' => 'RulesController@show',
                'as'   => 'admin.rules.show',
            ]);
            Route::get('rules/{id}/edit', [
                'uses' => 'RulesController@edit',
                'as'   => 'admin.rules.edit',
            ]);
            Route::post('rules/{id}/edit', [
                'uses' => 'RulesController@update',
                'as'   => 'admin.rules.update',
            ]);
            Route::get('rulesmatches', [
                'uses' => 'RulesController@showMatches',
                'as'   => 'admin.rules.matches',
            ]);
        });

        // roles
        Route::group([
            'middleware' => [
                'permission:roles-view',
            ],
        ], function () {
            Route::resource('roles', 'Admin\RoleController');
        });

        Route::group([
            'middleware' => [
                'permission:roles-manage',
            ],
        ], function () {
            Route::post('roles/{id}/edit', [
                'uses' => 'Admin\RoleController@update',
                'as'   => 'admin.roles.update',
            ]);
        });

        // permissions
        Route::group([
            'middleware' => [
                'permission:roles-permissions-view',
            ],
        ], function () {
            Route::resource('permissions', 'Admin\PermissionController');
        });

        Route::group([
            'middleware' => [
                'permission:roles-permissions-manage',
            ],
        ], function () {
            Route::post('permissions/{id}/edit', [
                'uses' => 'Admin\PermissionController@update',
                'as'   => 'admin.permissions.update',
            ]);
        });

        // report - nurse time report
        //these fall under the admin-access permission
        Route::get('reports/nurse/time', [
            'uses' => 'Admin\Reports\NurseTimeReportController@index',
            'as'   => 'admin.reports.nurseTime.index',
        ]);

        Route::get('reports/nurse/invoice', [
            'uses' => 'NurseController@makeInvoice',
            'as'   => 'admin.reports.nurse.invoice',
        ]);

        Route::post('reports/nurse/invoice/generate', [
            'uses' => 'NurseController@generateInvoice',
            'as'   => 'admin.reports.nurse.generate',
        ]);

        Route::get('reports/nurse/daily', [
            'uses' => 'NurseController@makeDailyReport',
            'as'   => 'admin.reports.nurse.daily',
        ]);
        Route::get('reports/nurse/daily/data', [
            'uses' => 'NurseController@dailyReport',
            'as'   => 'admin.reports.nurse.daily.data',
        ]);

        Route::get('reports/nurseTime/exportxls', [
            'uses' => 'Admin\Reports\NurseTimeReportController@exportxls',
            'as'   => 'admin.reports.nurseTime.exportxls',
        ]);


        //STATS

        Route::get('reports/nurse/stats', [
            'uses' => 'NurseController@makeHourlyStatistics',
            'as'   => 'stats.nurse.info',
        ]);

        // questions
        Route::group([
            'middleware' => [
                'permission:programs-manage',
            ],
        ], function () {
            Route::resource('questions', 'Admin\CPRQuestionController');
            Route::post('questions/{id}/edit', [
                'uses' => 'Admin\CPRQuestionController@update',
                'as'   => 'admin.questions.update',
            ]);
            Route::get('questions/{id}/destroy', [
                'uses' => 'Admin\CPRQuestionController@destroy',
                'as'   => 'admin.questions.destroy',
            ]);

            Route::resource('questionSets', 'Admin\CPRQuestionSetController');
            Route::post('questionSets', [
                'uses' => 'Admin\CPRQuestionSetController@index',
                'as'   => 'admin.questionSets',
            ]);
            Route::post('questionSets/{id}/edit', [
                'uses' => 'Admin\CPRQuestionSetController@update',
                'as'   => 'admin.questionSets.update',
            ]);
            Route::get('questionSets/{id}/destroy', [
                'uses' => 'Admin\CPRQuestionSetController@destroy',
                'as'   => 'admin.questionSets.destroy',
            ]);

            // items
            Route::resource('items', 'Admin\CPRItemController');
            Route::post('items/{id}/edit', [
                'uses' => 'Admin\CPRItemController@update',
                'as'   => 'admin.items.update',
            ]);
            Route::get('items/{id}/destroy', [
                'uses' => 'Admin\CPRItemController@destroy',
                'as'   => 'admin.items.destroy',
            ]);

            // ucp
            Route::resource('ucp', 'Admin\CPRUCPController');
            Route::post('ucp/{id}/edit', [
                'uses' => 'Admin\CPRUCPController@update',
                'as'   => 'admin.ucp.update',
            ]);
            Route::get('ucp/{id}/destroy', [
                'uses' => 'Admin\CPRUCPController@destroy',
                'as'   => 'admin.ucp.destroy',
            ]);
        });

        // observations
        Route::group([
            'middleware' => [
                'permission:observations-create',
            ],
        ], function () {
            Route::resource('comments', 'Admin\CommentController');
            Route::resource('observations', 'Admin\ObservationController');
        });

        Route::group([
            'middleware' => [
                'permission:observations-edit',
            ],
        ], function () {
            Route::get('observations/{id}/destroy', [
                'uses' => 'Admin\ObservationController@destroy',
                'as'   => 'admin.observations.destroy',
            ]);
            Route::post('observations/{id}/edit', [
                'uses' => 'Admin\ObservationController@update',
                'as'   => 'admin.observations.update',
            ]);

            Route::post('comments/{id}/edit', [
                'uses' => 'Admin\CommentController@update',
                'as'   => 'admin.comments.update',
            ]);
            Route::get('comments/{id}/destroy', [
                'uses' => 'Admin\CommentController@destroy',
                'as'   => 'admin.comments.destroy',
            ]);
        });

        Route::group([
            'middleware' => [
                'permission:observations-view',
            ],
        ], function () {
            Route::post('observations', [
                'uses' => 'Admin\ObservationController@index',
                'as'   => 'admin.observations',
            ]);
        });


        // programs
        Route::group([
            'middleware' => [
                'permission:programs-view',
            ],
        ], function () {
            Route::resource('programs', 'Admin\WpBlogController');
            Route::get('programs', [
                'uses' => 'Admin\WpBlogController@index',
                'as'   => 'admin.programs.index',
            ]);
            Route::get('programs/create', [
                'uses' => 'Admin\WpBlogController@create',
                'as'   => 'admin.programs.create',
            ]);
            Route::post('programs/create', [
                'uses' => 'Admin\WpBlogController@store',
                'as'   => 'admin.programs.store',
            ]);
            Route::get('programs/{id}', [
                'uses' => 'Admin\WpBlogController@show',
                'as'   => 'admin.programs.show',
            ]);
            Route::get('programs/{id}/edit', [
                'uses' => 'Admin\WpBlogController@edit',
                'as'   => 'admin.programs.edit',
            ]);
            Route::post('programs/{id}/edit', [
                'uses' => 'Admin\WpBlogController@update',
                'as'   => 'admin.programs.update',
            ]);
            Route::get('programs/{id}/destroy', [
                'uses' => 'Admin\WpBlogController@destroy',
                'as'   => 'admin.programs.destroy',
            ]);
            Route::get('programs/{id}/questions', [
                'uses' => 'Admin\WpBlogController@showQuestions',
                'as'   => 'admin.programs.questions',
            ]);

            // locations
            Route::resource('locations', 'LocationController');
            Route::get('locations', [
                'uses' => 'LocationController@index',
                'as'   => 'locations.index',
            ]);
            Route::get('locations/{id}', [
                'uses' => 'LocationController@show',
                'as'   => 'locations.show',
            ]);
            Route::get('locations/{id}/edit', [
                'uses' => 'LocationController@edit',
                'as'   => 'locations.edit',
            ]);
            Route::post('locations/update', [
                'uses' => 'LocationController@update',
                'as'   => 'locations.update',
            ]);
        });

        // apikeys
        Route::group([
            'middleware' => [
                'permission:apikeys-view',
            ],
        ], function () {
            Route::resource('apikeys', 'Admin\ApiKeyController', [
                'only' => [
                    'index',
                    'destroy',
                    'store',
                ],
            ]);
        });


        // care items
        Route::group([
            'middleware' => [
                'permission:programs-manage',
            ],
        ], function () {
            Route::resource('careitems', 'Admin\CareItemController');
            Route::post('careitems/{id}/edit', [
                'uses' => 'Admin\CareItemController@update',
                'as'   => 'admin.careitems.update',
            ]);
            Route::get('careitems/{id}/destroy', [
                'uses' => 'Admin\CareItemController@destroy',
                'as'   => 'admin.careitems.destroy',
            ]);

            // care plans
            Route::resource('careplans', 'Admin\CarePlanController');
            Route::post('careplans/{id}/edit', [
                'uses' => 'Admin\CarePlanController@update',
                'as'   => 'admin.careplans.update',
            ]);
            Route::post('careplans/{id}/duplicate', [
                'uses' => 'Admin\CarePlanController@duplicate',
                'as'   => 'admin.careplans.duplicate',
            ]);
            Route::get('careplans/{id}/destroy', [
                'uses' => 'Admin\CarePlanController@destroy',
                'as'   => 'admin.careplans.destroy',
            ]);

            // care plan sections
            Route::resource('careplansections', 'Admin\CarePlanSectionController');
            Route::post('careplansections/{id}/edit', [
                'uses' => 'Admin\CarePlanSectionController@update',
                'as'   => 'admin.careplansections.update',
            ]);
            Route::get('careplansections/{id}/destroy', [
                'uses' => 'Admin\CarePlanSectionController@destroy',
                'as'   => 'admin.careplansections.destroy',
            ]);
        });
    });


    /*
     *
     * CARE-CENTER GROUP
     *
     */
    Route::group([
        'middleware' => ['role:care-center'],
        'prefix'     => 'care-center',
    ], function () {

        Route::resource('work-schedule', 'CareCenter\WorkScheduleController', [
            'only'  => [
                'index',
                'store',
            ],
            'names' => [
                'index' => 'care.center.work.schedule.index',
                'store' => 'care.center.work.schedule.store',
            ],
        ]);

        Route::get('work-schedule/destroy/{id}', [
            'uses' => 'CareCenter\WorkScheduleController@destroy',
            'as'   => 'care.center.work.schedule.destroy',
        ]);
    });

});

/*
 * Third Party Apis Config Pages
 */
Route::group(['prefix' => 'third-party-api-settings'], function () {
    Route::resource('redox-engine', 'Redox\ConfigController', [
        'except' => [
            'index',
            'destroy',
            'show',
        ],
    ]);

    Route::resource('qliqsoft', 'qliqSOFT\ConfigController', [
        'except' => [
            'index',
            'destroy',
            'show',
        ],
    ]);
});


/***********************/
/***********************/
//     API ROUTES
/***********************/
/***********************/

// pagetimer
Route::group([], function () {
    //Route::get('pagetimer', 'PageTimerController@store');
    Route::post('api/v2.1/pagetimer', [
        'uses' => 'PageTimerController@store',
        'as'   => 'api.pagetracking',
    ]);
    Route::post('callupdate', [
        'uses' => 'CallController@update',
        'as'   => 'api.callupdate',
    ]);
    Route::post('callcreate', [
        'uses' => 'CallController@create',
        'as'   => 'api.callcreate',
    ]);
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
Route::group([
    'before'     => 'jwt-auth',
    'prefix'     => 'wp/api/v2.1',
    'middleware' => 'authApiCall',
], function () {
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
Route::group(['prefix' => 'cron'], function () {
    Route::get('/scheduler/{id}', function ($id) {
        $msgScheduler = new \App\Services\MsgScheduler();
        $msgScheduler->index($id);
    });
});

Route::controller('datatables', 'DatatablesController', [
    'anyData'            => 'datatables.data',
    'anyCallsManagement' => 'datatables.anyCallsManagement',
    'getIndex'           => 'datatables',
]);

Route::get('datatables/callData', [
    'uses' => 'DatatablesController@callData',
    'as'   => 'datatables.callData',
]);