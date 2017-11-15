<?php

use App\Call;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//Call Lists TEMP
//(new App\Http\Controllers\Admin\WelcomeCallListController(new \Illuminate\Http\Request()))->makePhoenixHeartCallList();

Route::post('send-sample-fax', 'DemoController@sendSampleEfaxNote');

Route::post('/send-sample-direct-mail', 'DemoController@sendSampleEMRNote');

//Patient Landing Pages
Route::resource('sign-up', 'PatientSignupController');
Route::get('talk-to-us', 'PatientSignupController@talkToUs');

//if (app()->environment() != 'production') {
//    //test route
//    Route::get('/sms/test', 'TwilioController@sendTestSMS');
//
//    Route::get('/rohan', function () {
//
//        $date = Carbon::parse('2017-08-07');
//
//        $countMade =
//            Call::where('outbound_cpm_id', 2159)
//                ->where('scheduled_date', '2017-08-07')
////                ->where(function ($q) use ($date){
////                    $q->where('called_date', '>=', $date->startOfDay()->toDateTimeString())
////                        ->where('called_date', '<=', $date->endOfDay()->toDateTimeString());
////                })
//                ->count();
//
//        return $countMade;
//    });
//}

//Algo test routes.

Route::group(['prefix' => 'algo'], function () {

   Route::get('family', 'AlgoTestController@algoFamily');

   Route::get('cleaner', 'AlgoTestController@algoCleaner');

   Route::get('tuner', 'AlgoTestController@algoTuner');

   Route::get('rescheduler', 'AlgoTestController@algoRescheduler');

});


Route::get('ajax/patients', 'UserController@getPatients');

/*
 * NO AUTHENTICATION NEEDED FOR THESE ROUTES
 */
Route::post('account/login', 'Patient\PatientController@patientAjaxSearch');

Route::get('/', 'WelcomeController@index');
Route::get('home', 'WelcomeController@index');

Route::get('login', 'Auth\LoginController@showLoginForm');

Route::group([
    'prefix'     => 'auth',
    'middleware' => 'web',
], function () {
    Auth::routes();

    Route::get('logout', 'Auth\LoginController@logout');
});

/****************************/
/****************************/
//    AUTH ROUTES
/****************************/
/****************************/
Route::group(['middleware' => 'auth'], function () {

    Route::get('cache/view/{key}', [
        'as' => 'get.cached.view.by.key',
        'uses' => 'Cache\UserCacheController@getCachedViewByKey',
    ]);

    Route::view('jobs/completed','admin.jobsCompleted.manage');

    Route::get('download/{filePath}', [
        'uses' => 'DownloadController@file',
        'as'   => 'download',
    ]);

    /**
     * API
     */
    Route::group(['prefix' => 'api'], function () {
        Route::get('practices/all', 'API\PracticeController@allPracticesWithLocationsAndStaff');

        Route::get('calls-management', [
            'uses' => 'API\Admin\CallsController@toBeDeprecatedIndex',
            'as'   => 'call.anyCallsManagement',
        ]);

        Route::resource('profile', 'API\ProfileController');

        Route::resource('nurses', 'API\NurseController');


    });

    Route::resource('profiles', 'API\ProfileController');

    Route::resource('user.care-plan', 'API\PatientCarePlanController');
    Route::resource('user.care-team', 'API\CareTeamController');
    Route::resource('practice.locations', 'API\PracticeLocationsController');
    Route::resource('practice.users', 'API\PracticeStaffController');

    Route::get('provider/search', [
        'uses' => 'API\CareTeamController@searchProviders',
        'as'   => 'providers.search',
    ]);

    Route::delete('pdf/{id}', 'API\PatientCarePlanController@deletePdf');

    Route::post('care-plans/{careplan_id}/pdfs', 'API\PatientCarePlanController@uploadPdfs');

    Route::get('download-pdf-careplan/{filePath}', [
        'uses' => 'API\PatientCarePlanController@downloadPdf',
        'as'   => 'download.pdf.careplan',
    ]);

    Route::patch('work-hours/{id}', 'CareCenter\WorkScheduleController@updateDailyHours');
// end API

    Route::resource('settings/email', 'EmailSettingsController');

    Route::get('/CCDModels/Items/MedicationListItem', 'CCDModels\Items\MedicationListItemController@index');
    Route::post('/CCDModels/Items/MedicationListItem/store', 'CCDModels\Items\MedicationListItemController@store');
    Route::post(
        '/CCDModels/Items/MedicationListItem/update',
        'CCDModels\Items\MedicationListItemController@update'
    );
    Route::post(
        '/CCDModels/Items/MedicationListItem/destroy',
        'CCDModels\Items\MedicationListItemController@destroy'
    );

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
    Route::get('ccd/show/user/{userId}', [
        'uses' => 'CCDViewer\CCDViewerController@showByUserId',
        'as'   => 'get.CCDViewerController.showByUserId',
    ]);

    Route::get('ccd/show/{ccdaId}', [
        'uses' => 'CCDViewer\CCDViewerController@show',
        'as'   => 'get.CCDViewerController.show',
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

    Route::get('imported-medical-records/{imrId}/training-results', [
        'uses' => 'ImporterController@getTrainingResults',
        'as'   => 'get.importer.training.results',
    ]);

    Route::post('importer/train', [
        'uses' => 'ImporterController@train',
        'as'   => 'post.train.importing.algorithm',
    ]);

    Route::post('importer/train/store', [
        'uses' => 'ImporterController@storeTrainingFeatures',
        'as'   => 'post.store.training.features',
    ]);

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
            'uses' => 'ImporterController@create',
            'as'   => 'import.ccd',
        ]);

        Route::post('imported-medical-records', [
            'uses' => 'ImporterController@uploadRawFiles',
            'as'   => 'upload.ccda',
        ]);
        Route::get('imported-medical-records', [
            'uses' => 'ImporterController@index',
            'as'   => 'view.files.ready.to.import',
        ]);

        Route::post('import', 'MedicalRecordImportController@import');

        Route::get('uploaded-ccd-items/{importedMedicalRecordId}/edit', 'ImportedMedicalRecordController@edit');

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

        Route::get('switch-to-web-careplan/{carePlanId}', [
            'uses' => 'Patient\PatientCareplanController@switchToWebMode',
            'as'   => 'switch.to.web.careplan',
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

        Route::get('approve-careplan/{viewNext?}', [
            'uses' => 'ProviderController@approveCarePlan',
            'as'   => 'patient.careplan.approve',
        ]);

        Route::get('view-careplan/pdf', [
            'uses' => 'ReportsController@viewPdfCarePlan',
            'as'   => 'patient.pdf.careplan.print',
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

            Route::group(['middleware' => 'check.careplan.mode'], function () {
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
        });


        // appointments
        Route::group(['prefix' => 'appointments'], function () {
            Route::get('create', [
                'uses' => 'AppointmentController@create',
                'as'   => 'patient.appointment.create',
            ]);
            Route::post('store', [
                'uses' => 'AppointmentController@store',
                'as'   => 'patient.appointment.store',
            ]);
            Route::get('', [
                'uses' => 'AppointmentController@index',
                'as'   => 'patient.appointment.index',
            ]);
            Route::get('view/{appointmentId}', [
                'uses' => 'AppointmentController@view',
                'as'   => 'patient.appointment.view',
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
            Route::post('{noteId}/addendums', [
                'uses' => 'NotesController@storeAddendum',
                'as'   => 'note.store.addendum',
            ]);
        });

        Route::post('ccm/toggle', [
            'uses' => 'CCMComplexToggleController@toggle',
            'as'   => 'patient.ccm.toggle',
        ]);


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
            'auth',
            'permission:admin-access',
        ],
        'prefix'     => 'admin',
    ], function () {

        Route::resource('medication-groups-maps', 'MedicationGroupsMapController');

        Route::post('get-athena-ccdas', [
            'uses' => 'CcdApi\Athena\AthenaApiController@getCcdas',
            'as'   => 'get.athena.ccdas',
        ]);

//        Route::get('nursecalls/{from}/{to}', function (
//            $from,
//            $to
//        ) {
//
//            $nurses = App\Nurse::all();
//            $data = [];
//            $total = 0;
//
//            foreach ($nurses as $nurse) {
//                $data[$nurse->user->fullName] = (new \App\Billing\NurseMonthlyBillGenerator(
//                    $nurse,
//                    \Carbon\Carbon::now()->subMonths($from),
//                    \Carbon\Carbon::now()->subMonths($to),
//                    false
//                ))->getCallsPerHourOverPeriod();
//
//                $total += $data[$nurse->user->fullName]['calls/hour'];
//            }
//
//            $data['AVERAGE'] = $total / $nurses->count();
//
//            return $data;
//        });

        /**
         * LOGGER
         */
        Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

        Route::get('nurses/windows', [
            'uses' => 'CareCenter\WorkScheduleController@getAllNurseSchedules',
            'as'   => 'get.admin.nurse.schedules',
        ]);

        Route::get('enrollment/list', [
            'uses' => 'Enrollment\EnrollmentConsentController@makeEnrollmentReport',
            'as'   => 'patient.enroll.makeReport',
        ]);

        Route::get('enrollment/list/data', [
            'uses' => 'Enrollment\EnrollmentConsentController@index',
            'as'   => 'patient.enroll.index',
        ]);

        Route::get('enrollment/ambassador/kpis', [
            'uses' => 'Enrollment\EnrollmentStatsController@makeAmbassadorStats',
            'as'   => 'enrollment.ambassador.stats',
        ]);

        Route::get('enrollment/ambassador/kpis/data', [
            'uses' => 'Enrollment\EnrollmentStatsController@ambassadorStats',
            'as'   => 'enrollment.ambassador.stats.data',
        ]);

        Route::get('enrollment/practice/kpis', [
            'uses' => 'Enrollment\EnrollmentStatsController@makePracticeStats',
            'as'   => 'enrollment.practice.stats',
        ]);

        Route::get('enrollment/practice/kpis/data', [
            'uses' => 'Enrollment\EnrollmentStatsController@practiceStats',
            'as'   => 'enrollment.practice.stats.data',
        ]);

        Route::get('invites/create', [
            'uses' => 'InviteController@create',
            'as'   => 'invite.create',
        ]);

        Route::post('invites/store', [
            'uses' => 'InviteController@store',
            'as'   => 'invite.store',
        ]);

        Route::patch('nurses/window/{id}', [
            'uses' => 'CareCenter\WorkScheduleController@patchAdminEditWindow',
            'as'   => 'patch.admin.edit.nurse.schedules',
        ]);

        Route::get('athena/ccdas/check', 'CcdApi\Athena\AthenaApiController@getTodays');

        Route::get('athena/ccdas/{practiceId}/{departmentId}', 'CcdApi\Athena\AthenaApiController@fetchCcdas');

        Route::post('calls/import', [
            'uses' => 'CallController@import',
            'as'   => 'post.CallController.import',
        ]);

        Route::post('make-welcome-call-list', [
            'uses' => 'Admin\WelcomeCallListController@makeWelcomeCallList',
            'as'   => 'make.welcome.call.list',
        ]);

        Route::get('families/create', [
            'uses' => 'FamilyController@create',
            'as'   => 'family.create',
        ]);

        Route::post('general-comments/import', [
            'uses' => 'Admin\UploadsController@postGeneralCommentsCsv',
            'as'   => 'post.GeneralCommentsCsv',
        ]);

        Route::get('calls/remix', [
            'uses' => 'Admin\PatientCallManagementController@remix',
            'as'   => 'admin.patientCallManagement.remix',
        ]);

        Route::get('calls/{patientId}', 'CallController@showCallsForPatient');


        Route::group([
            'prefix' => 'reports',
        ], function () {

            Route::post('monthly-billing', [
                'uses' => 'Admin\Reports\MonthlyBillingReportsController@makeMonthlyReport',
                'as'   => 'MonthlyBillingReportsController.makeMonthlyReport',
            ]);

            Route::group([
                'prefix' => 'monthly-billing/v2',
            ], function () {

                Route::get('/make', [
                    'uses' => 'Billing\PracticeInvoiceController@make',
                    'as'   => 'monthly.billing.make',
                ]);

                Route::post('/data', [
                    'uses' => 'Billing\PracticeInvoiceController@data',
                    'as'   => 'monthly.billing.data',
                ]);

                Route::post('/updateApproved', [
                    'uses' => 'Billing\PracticeInvoiceController@updateApproved',
                    'as'   => 'monthly.billing.approve',
                ]);

                Route::post('/counts', [
                    'uses' => 'Billing\PracticeInvoiceController@counts',
                    'as'   => 'monthly.billing.count',
                ]);

                Route::post('/storeProblem', [
                    'uses' => 'Billing\PracticeInvoiceController@storeProblem',
                    'as'   => 'monthly.billing.store-problem',
                ]);

                Route::post('/getBillingCount', [
                    'uses' => 'Billing\PracticeInvoiceController@getCounts',
                    'as'   => 'monthly.billing.counts',
                ]);

                Route::post('/updateRejected', [
                    'uses' => 'Billing\PracticeInvoiceController@updateRejected',
                    'as'   => 'monthly.billing.reject',
                ]);

                Route::post('/send', [
                    'uses' => 'Billing\PracticeInvoiceController@send',
                    'as'   => 'monthly.billing.send',
                ]);
            });

            Route::get('patients-for-insurance-check', [
                'uses' => 'Reports\PatientsForInsuranceCheck@make',
                'as'   => 'get.patients.for.insurance.check',
            ]);

            Route::group([
                'prefix' => 'sales',
            ], function () {

                //LOCATIONS -hidden on adminUI currently.

                Route::get('location/create', [
                    'uses' => 'SalesReportsController@createLocationReport',
                    'as'   => 'reports.sales.location.create',
                ]);

                Route::post('location/report', [
                    'uses' => 'SalesReportsController@makeLocationReport',
                    'as'   => 'reports.sales.location.report',
                ]);

                //PROVIDERS

                Route::get('provider/create', [
                    'uses' => 'SalesReportsController@createProviderReport',
                    'as'   => 'reports.sales.provider.create',
                ]);

                Route::post('provider/report', [
                    'uses' => 'SalesReportsController@makeProviderReport',
                    'as'   => 'reports.sales.provider.report',
                ]);

                //PRACTICES

                Route::get('practice/create', [
                    'uses' => 'SalesReportsController@createPracticeReport',
                    'as'   => 'reports.sales.practice.create',
                ]);

                Route::post('practice/report', [
                    'uses' => 'SalesReportsController@makePracticeReport',
                    'as'   => 'reports.sales.practice.report',
                ]);
            });

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

        //Practice Billing
        Route::group(['prefix' => 'practice/billing'], function () {

            Route::get('create', [
                'uses' => 'Billing\PracticeInvoiceController@createInvoices',
                'as'   => 'practice.billing.create',
            ]);

            Route::post('make', [
                'uses' => 'Billing\PracticeInvoiceController@makeInvoices',
                'as'   => 'practice.billing.make',
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
            Route::get('users/createQuickPatient/{primaryProgramId}', [
                'uses' => 'UserController@createQuickPatient',
                'as'   => 'admin.users.createQuickPatient',
            ]);
            Route::post('users/createQuickPatient/', [
                'uses' => 'UserController@storeQuickPatient',
                'as'   => 'admin.users.storeQuickPatient',
            ]);
            Route::get('users/{id}/msgcenter', [
                'uses' => 'UserController@showMsgCenter',
                'as'   => 'admin.users.msgCenter',
            ]);
            Route::post('users/{id}/msgcenter', [
                'uses' => 'UserController@showMsgCenter',
                'as'   => 'admin.users.msgCenterUpdate',
            ]);
            Route::get('calls/', [
                'uses' => 'Admin\PatientCallManagementController@index',
                'as'   => 'admin.patientCallManagement.index',
            ]);
            Route::get('calls/{id}/edit', [
                'uses' => 'Admin\PatientCallManagementController@edit',
                'as'   => 'admin.patientCallManagement.edit',
            ]);
            Route::post('calls/{id}/edit', [
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

        Route::post('reports/nurse/invoice/send', [
            'uses' => 'NurseController@sendInvoice',
            'as'   => 'admin.reports.nurse.send',
        ]);

        Route::get('reports/nurse/daily', [
            'uses' => 'NurseController@makeDailyReport',
            'as'   => 'admin.reports.nurse.daily',
        ]);

        Route::get('reports/nurse/daily/data', [
            'uses' => 'NurseController@dailyReport',
            'as'   => 'admin.reports.nurse.daily.data',
        ]);

        Route::get('reports/nurse/allocation', [
            'uses' => 'NurseController@monthlyOverview',
            'as'   => 'admin.reports.nurse.allocation',
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
                'as'   => 'admin.observations.index',
            ]);
        });


        // programs
        Route::group([
            'middleware' => [
                'permission:programs-view',
            ],
        ], function () {
            Route::resource('programs', 'Admin\PracticeController');
            Route::get('programs', [
                'uses' => 'Admin\PracticeController@index',
                'as'   => 'admin.programs.index',
            ]);
            Route::get('programs/create', [
                'uses' => 'Admin\PracticeController@create',
                'as'   => 'admin.programs.create',
            ]);
            Route::post('programs/create', [
                'uses' => 'Admin\PracticeController@store',
                'as'   => 'admin.programs.store',
            ]);
            Route::get('programs/{id}', [
                'uses' => 'Admin\PracticeController@show',
                'as'   => 'admin.programs.show',
            ]);
            Route::get('programs/{id}/edit', [
                'uses' => 'Admin\PracticeController@edit',
                'as'   => 'admin.programs.edit',
            ]);
            Route::post('programs/{id}/edit', [
                'uses' => 'Admin\PracticeController@update',
                'as'   => 'admin.programs.update',
            ]);
            Route::get('programs/{id}/destroy', [
                'uses' => 'Admin\PracticeController@destroy',
                'as'   => 'admin.programs.destroy',
            ]);
            Route::get('programs/{id}/questions', [
                'uses' => 'Admin\PracticeController@showQuestions',
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
    });


    /*
     *
     * CARE-CENTER GROUP
     *
     */
    Route::group([
        'middleware' => ['role:care-center|administrator'],
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

        Route::post('work-schedule/holidays', [
            'uses' => 'CareCenter\WorkScheduleController@storeHoliday',
            'as'   => 'care.center.work.schedule.holiday.store',
        ]);

        Route::get('work-schedule/holidays/destroy/{id}', [
            'uses' => 'CareCenter\WorkScheduleController@destroyHoliday',
            'as'   => 'care.center.work.schedule.holiday.destroy',
        ]);
    });
});

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
 *
 * Provider Dashboard
 *
 */
Route::group([
    'prefix'     => '{practiceSlug}/admin',
    'middleware' => [
        'auth',
        'providerDashboardACL:administrator',
    ],
], function () {

    Route::post('invite', [
        'uses' => 'Provider\DashboardController@postStoreInvite',
        'as'   => 'post.store.invite',
    ]);

    Route::post('locations', [
        'uses' => 'Provider\DashboardController@postStoreLocations',
        'as'   => 'provider.dashboard.store.locations',
    ]);

    Route::post('staff', [
        'uses' => 'Provider\DashboardController@postStoreStaff',
        'as'   => 'provider.dashboard.store.staff',
    ]);

    Route::post('notifications', [
        'uses' => 'Provider\DashboardController@postStoreNotifications',
        'as'   => 'provider.dashboard.store.notifications',
    ]);

    Route::get('notifications', [
        'uses' => 'Provider\DashboardController@getCreateNotifications',
        'as'   => 'provider.dashboard.manage.notifications',
    ]);

    Route::post('practice', [
        'uses' => 'Provider\DashboardController@postStorePractice',
        'as'   => 'provider.dashboard.store.practice',
    ]);

    Route::get('practice', [
        'uses' => 'Provider\DashboardController@getCreatePractice',
        'as'   => 'provider.dashboard.manage.practice',
    ]);

    Route::get('staff', [
        'uses' => 'Provider\DashboardController@getCreateStaff',
        'as'   => 'provider.dashboard.manage.staff',
    ]);

    Route::get('', [
//        'uses' => 'Provider\DashboardController@getIndex',
    'uses' => 'Provider\DashboardController@getCreateNotifications',
    'as'   => 'provider.dashboard.index',
    ]);

    Route::get('locations', [
        'uses' => 'Provider\DashboardController@getCreateLocation',
        'as'   => 'provider.dashboard.manage.locations',
    ]);
});

/*
 * Enrollment Center UI
 */

Route::group([
    'prefix' => '/enrollment',
], function () {

    Route::post('/sms/reply', [
        'uses' => 'Enrollment\EnrollmentSMSController@handleIncoming',
        'as'   => 'enrollment.sms.reply',
    ]);

    Route::group(['middleware' => 'auth'], function () {
        Route::get('/', [
            'uses' => 'Enrollment\EnrollmentCenterController@dashboard',
            'as'   => 'enrollment-center.dashboard',
        ]);

        Route::post('/consented', [
            'uses' => 'Enrollment\EnrollmentCenterController@consented',
            'as'   => 'enrollment-center.consented',
        ]);

        Route::post('/utc', [
            'uses' => 'Enrollment\EnrollmentCenterController@unableToContact',
            'as'   => 'enrollment-center.utc',
        ]);

        Route::post('/rejected', [
            'uses' => 'Enrollment\EnrollmentCenterController@rejected',
            'as'   => 'enrollment-center.rejected',
        ]);
    });
});

/*
 * Enrollment Consent
 */

Route::group([
    'prefix' => 'join',
], function () {

    Route::post('/save', [
        'uses' => 'Enrollment\EnrollmentConsentController@store',
        'as'   => 'patient.enroll.store',
    ]);

    Route::get('{invite_code}', [
        'uses' => 'Enrollment\EnrollmentConsentController@create',
        'as'   => 'patient.enroll.create',
    ]);

    Route::post('/update', [
        'uses' => 'Enrollment\EnrollmentConsentController@update',
        'as'   => 'patient.enroll.update',
    ]);
});

Route::group([
    'prefix' => 'onboarding',
], function () {

    Route::get('create-invited-user/{code?}', [
        'middleware' => 'verify.invite',
        'uses'       => 'Provider\OnboardingController@getCreateInvitedUser',
        'as'         => 'get.onboarding.create.invited.user',
    ]);

    Route::get('create-practice-lead-user/{code?}', [
        'middleware' => 'verify.invite',
        'uses'       => 'Provider\OnboardingController@getCreatePracticeLeadUser',
        'as'         => 'get.onboarding.create.program.lead.user',
    ]);

    Route::post('store-invited-user', [
        'uses' => 'Provider\OnboardingController@postStoreInvitedUser',
        'as'   => 'get.onboarding.store.invited.user',
    ]);

    Route::post('store-practice-lead-user', [
        'uses' => 'Provider\OnboardingController@postStorePracticeLeadUser',
        'as'   => 'post.onboarding.store.program.lead.user',
    ]);


    Route::group([
        'middleware' => 'auth',
    ], function () {
        Route::post('store-locations/{lead_id}', [
            'uses' => 'Provider\OnboardingController@postStoreLocations',
            'as'   => 'post.onboarding.store.locations',
        ]);

        Route::post('store-practice/{lead_id}', [
            'uses' => 'Provider\OnboardingController@postStorePractice',
            'as'   => 'post.onboarding.store.practice',
        ]);

        Route::get('{practiceSlug}/locations/create/{lead_id}', [
            'uses' => 'Provider\OnboardingController@getCreateLocations',
            'as'   => 'get.onboarding.create.locations',
        ]);

        Route::get('create-practice/{lead_id}', [
            'uses' => 'Provider\OnboardingController@getCreatePractice',
            'as'   => 'get.onboarding.create.practice',
        ]);

        Route::get('{practiceSlug}/staff/create', [
            'uses' => 'Provider\OnboardingController@getCreateStaff',
            'as'   => 'get.onboarding.create.staff',
        ]);

        Route::post('{practiceSlug}/store-staff', [
            'uses' => 'Provider\OnboardingController@postStoreStaff',
            'as'   => 'post.onboarding.store.staff',
        ]);
    });
});

//This route was replaced by route with url '/downloadInvoice/{practice}/{name}', and name 'monthly.billing.download'.
//We keep it here to support Report links mailed before 5/12/17.
Route::get('/admin/reports/monthly-billing/v2/downloadInvoice/{practice}/{name}', [
    'uses'       => 'Billing\PracticeInvoiceController@downloadInvoice',
    'middleware' => ['auth'],
]);

Route::get('/downloadInvoice/{practice}/{name}', [
    'uses'       => 'Billing\PracticeInvoiceController@downloadInvoice',
    'as'         => 'monthly.billing.download',
    'middleware' => ['auth'],
]);

Route::group([
    'prefix' => 'twilio',
], function () {


    Route::post('/token', [
        'uses' => 'TwilioController@obtainToken',
        'as'   => 'twilio.token',
    ]);

    Route::post('/call/make', [
        'uses' => 'TwilioController@newCall',
        'as'   => 'twilio.call',
    ]);

    Route::get('/call', 'TwilioController@makeCall');
});
