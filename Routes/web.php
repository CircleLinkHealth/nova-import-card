<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

//
// ADMIN (/admin)
//
// NOTE: in two route groups. One for software-only and one for super admins
//
Route::prefix('cpmadmin')->group(function () {
    Route::get('upg0506/{type}', [
        'uses' => 'Admin\DashboardController@upg0506',
        'as'   => 'upg0506.demo',
    ])->middleware('auth');
    
    Route::group(['prefix' => 'api'], function () {
        Route::group(['prefix' => 'admin'], function () {
            Route::get('clear-cache/{key}', [
                'uses' => 'DashboardController@clearCache',
                'as' => 'clear.cache.key',
            ])->middleware('permission:call.read');
            //the new calls route that uses calls-view table
            Route::get('calls-v2', [
                'uses' => 'API\CallsViewController@index',
                'as' => 'calls.v2.index',
            ])->middleware('permission:call.read');
        
            Route::group(['prefix' => 'calls'], function () {
                Route::get('', [
                    'uses' => 'API\Admin\CallsController@index',
                    'as' => 'calls.index',
                ])->middleware('permission:call.read');
            
                Route::get('{id}', [
                    'uses' => 'API\Admin\CallsController@show',
                    'as' => 'calls.show',
                ])->middleware('permission:call.read');
            
                Route::delete('{ids}', [
                    'uses' => 'API\Admin\CallsController@remove',
                    'as' => 'calls.destroy',
                ])->middleware('permission:call.delete');
            });
        
            Route::post(
                'user.outbound-calls',
                'API\UserOutboundCallController@store'
            )->middleware('permission:call.create');
        });
    });
    
    Route::group([
        'middleware' => [
            'auth',
            'permission:admin-access,practice-admin',
        ],
        'prefix' => 'admin',
    ], function () {
        Route::get('opcache', 'Admin\OPCacheGUIController@index');

        Route::get('calls-v2', [
            'uses' => 'PatientCallManagementController@remixV2',
            'as'   => 'admin.patientCallManagement.v2.index',
        ]);

        Route::group([
            'prefix' => 'reports',
        ], function () {
            Route::group([
                'prefix' => 'monthly-billing/v2',
            ], function () {
            Route::get('/make', [
                'uses' => 'Billing\PracticeInvoiceController@make',
                'as'   => 'monthly.billing.make',
            ])->middleware('permission:patientSummary.read,patientProblem.read,chargeableService.read,practice.read');

            Route::post('/data', [
                'uses' => 'Billing\PracticeInvoiceController@data',
                'as'   => 'monthly.billing.data',
            ])->middleware('permission:patientSummary.read,patientSummary.update,patientSummary.create');

            Route::get('/counts', [
                'uses' => 'Billing\PracticeInvoiceController@counts',
            ])->middleware('permission:patientSummary.read');

            Route::post('/close', [
                'uses' => 'Billing\PracticeInvoiceController@closeMonthlySummaryStatus',
                'as'   => 'monthly.billing.close.month',
            ])->middleware('permission:patientSummary.update');

            Route::post('/open', [
                'uses' => 'Billing\PracticeInvoiceController@openMonthlySummaryStatus',
                'as'   => 'monthly.billing.open.month',
            ])->middleware('permission:patientSummary.update');

            Route::post('/status/update', [
                'uses' => 'Billing\PracticeInvoiceController@updateStatus',
                'as'   => 'monthly.billing.status.update',
            ])->middleware('permission:patientSummary.update');
        });
        });
    });

    Route::group([
        'middleware' => [
            'auth',
            'permission:admin-access',
        ],
        'prefix' => 'admin',
    ], function () {
        Route::group(['prefix' => 'offline-activity-time-requests'], function () {
            Route::get('', [
                'uses' => 'OfflineActivityTimeRequestController@adminIndex',
                'as'   => 'admin.offline-activity-time-requests.index',
            ])->middleware('permission:patient.read,offlineActivityRequest.read');
            Route::post('respond', [
                'uses' => 'OfflineActivityTimeRequestController@adminRespond',
                'as'   => 'admin.offline-activity-time-requests.respond',
            ])->middleware('permission:patient.read,offlineActivityRequest.read');
        });

        Route::group(['prefix' => 'direct-mail'], function () {
            Route::get('{directMailId}', [
                'uses' => 'DirectMailController@show',
                'as'   => 'direct-mail.show',
            ]);

            Route::get('inbox/check', [
                'uses' => 'DirectMailController@checkInbox',
                'as'   => 'direct-mail.check',
            ]);

            Route::post('new', [
                'uses' => 'DirectMailController@send',
                'as'   => 'direct-mail.send',
            ]);
        });

        Route::group(['prefix' => 'revisions'], function () {
            Route::get('all-activity', [
                'uses' => 'ShowRevisionsController@allActivity',
                'as'   => 'revisions.all.activity',
            ]);

            Route::get('phi-activity', [
                'uses' => 'ShowRevisionsController@allActivity',
                'as'   => 'revisions.phi.activity',
            ]);

            Route::get('{userId}/phi', [
                'uses' => 'ShowRevisionsController@phi',
                'as'   => 'revisions.patient.phi',
            ]);
        });

        Route::group(['prefix' => 'demo'], function () {
            Route::get('create', 'Demo\SendSampleNoteController@showMakeNoteForm');

            Route::post('make-pdf', [
                'as'   => 'demo.note.make.pdf',
                'uses' => 'Demo\SendSampleNoteController@makePdf',
            ])->middleware('permission:practice.read,note.create,careplan-pdf.create');

            Route::post('send-efax', [
                'as'   => 'demo.note.efax',
                'uses' => 'Demo\SendSampleNoteController@sendNoteViaEFax',
            ])->middleware('permission:note.send');
        });

        Route::group(['prefix' => 'ca-director'], function () {
            Route::get('', [
                'uses' => 'EnrollmentDirectorController@index',
                'as'   => 'ca-director.index',
            ]);

            Route::get('searchEnrollables', [
                'uses' => 'EnrollmentDirectorController@searchEnrollables',
                'as'   => 'enrollables.ca-director.search',
            ]);

            Route::get('/enrollees', [
                'uses' => 'EnrollmentDirectorController@getEnrollees',
                'as'   => 'ca-director.enrollees',
            ]);

            Route::get('/ambassadors', [
                'uses' => 'EnrollmentDirectorController@getCareAmbassadors',
                'as'   => 'ca-director.ambassadors',
            ]);

            Route::post('/assign-ambassador', [
                'uses' => 'EnrollmentDirectorController@assignCareAmbassadorToEnrollees',
                'as'   => 'ca-director.assign-ambassador',
            ]);

            Route::post('/assign-callback', [
                'uses' => 'EnrollmentDirectorController@assignCallback',
                'as'   => 'ca-director.assign-callback',
            ]);

            Route::post('/mark-ineligible', [
                'uses' => 'EnrollmentDirectorController@markEnrolleesAsIneligible',
                'as'   => 'ca-director.mark-ineligible',
            ]);

            Route::post('/unassign-ca', [
                'uses' => 'EnrollmentDirectorController@unassignCareAmbassadorFromEnrollees',
                'as'   => 'ca-director.unassign-ambassador',
            ]);

            Route::post('/edit-enrollee', [
                'uses' => 'EnrollmentDirectorController@editEnrolleeData',
                'as'   => 'ca-director.edit-enrollee',
            ]);

            Route::post('/add-enrollee-custom-filter', [
                'uses' => 'EnrollmentDirectorController@addEnrolleeCustomFilter',
                'as'   => 'ca-director.add-enrollee-custom-filter',
            ]);

            Route::get('/test-enrollees', [
                'uses' => 'EnrollmentDirectorController@runCreateEnrolleesSeeder',
                'as'   => 'ca-director.test-enrollees',
            ]);
        });

        Route::get(
            'saas-accounts/create',
            'Admin\CRUD\SaasAccountController@create'
        )->middleware('permission:saas.create');
        Route::post('saas-accounts', 'Admin\CRUD\SaasAccountController@store')->middleware('permission:saas.create');

        Route::view('api-clients', 'admin.manage-api-clients');

        Route::get('medication-groups-maps', [
            'uses' => 'MedicationGroupsMapController@index',
            'as'   => 'medication-groups-maps.index',
        ])->middleware('permission:medicationGroup.read');

        Route::post('medication-groups-maps', [
            'uses' => 'MedicationGroupsMapController@store',
            'as'   => 'medication-groups-maps.store',
        ])->middleware('permission:medicationGroup.create');

        Route::delete('medication-groups-maps/{id}', [
            'uses' => 'MedicationGroupsMapController@destroy',
            'as'   => 'medication-groups-maps.destroy',
        ])->middleware('permission:medicationGroup.delete');

        Route::post('get-athena-ccdas', [
            'uses' => 'CcdApi\Athena\AthenaApiController@getCcdas',
            'as'   => 'get.athena.ccdas',
        ])->middleware('permission:ccd-import');

        Route::post('athena-pull', [
            'uses' => 'DashboardController@pullAthenaEnrollees',
            'as'   => 'pull.athena.enrollees',
        ])->middleware('permission:batch.create,enrollee.create,enrollee.update');

        Route::get('patients/letters/paused', [
            'uses' => 'ReportsController@pausedPatientsLetterPrintList',
            'as'   => 'get.print.paused.letters',
        ])->middleware('permission:patient.read');

        Route::get('patients/letters/paused/file', [
            'uses' => 'ReportsController@getPausedLettersFile',
            'as'   => 'get.paused.letters.file',
        ])->middleware('permission:careplan-pdf.create,careplan-pdf.read,patient.read');

        Route::get('nurses/windows', [
            'uses' => 'CareCenter\WorkScheduleController@showAllNurseScheduleForAdmin',
            'as'   => 'get.admin.nurse.schedules',
        ])->middleware('permission:nurse.read');

        Route::get('enrollment/list', [
            'uses' => 'Enrollment\EnrollmentConsentController@makeEnrollmentReport',
            'as'   => 'patient.enroll.makeReport',
        ])->middleware('permission:enrollee.read,enrollee.update,call.read,practice.read,provider.read');

        Route::get('enrollment/list/data', [
            'uses' => 'Enrollment\EnrollmentConsentController@index',
            'as'   => 'patient.enroll.index',
        ])->middleware('permission:enrollee.read,enrollee.update,call.read,practice.read,provider.read');

        Route::get('enrollment/ambassador/kpis', [
            'uses' => 'Enrollment\EnrollmentStatsController@makeAmbassadorStats',
            'as'   => 'enrollment.ambassador.stats',
        ])->middleware('permission:ambassador.read');

        Route::get('enrollment/ambassador/kpis/excel', [
            'uses' => 'Enrollment\EnrollmentStatsController@ambassadorStatsExcel',
            'as'   => 'enrollment.ambassador.stats.excel',
        ])->middleware('permission:ambassador.read');

        Route::get('enrollment/ambassador/kpis/data', [
            'uses' => 'Enrollment\EnrollmentStatsController@ambassadorStats',
            'as'   => 'enrollment.ambassador.stats.data',
        ])->middleware('permission:ambassador.read');

        Route::get('enrollment/practice/kpis', [
            'uses' => 'Enrollment\EnrollmentStatsController@makePracticeStats',
            'as'   => 'enrollment.practice.stats',
        ])->middleware('permission:ambassador.read,practice.read');

        Route::get('enrollment/practice/kpis/excel', [
            'uses' => 'Enrollment\EnrollmentStatsController@practiceStatsExcel',
            'as'   => 'enrollment.practice.stats.excel',
        ])->middleware('permission:ambassador.read,practice.read');

        Route::get('enrollment/practice/kpis/data', [
            'uses' => 'Enrollment\EnrollmentStatsController@practiceStats',
            'as'   => 'enrollment.practice.stats.data',
        ])->middleware('permission:ambassador.read,practice.read');

        Route::patch('nurses/window/{id}', [
            'uses' => 'CareCenter\WorkScheduleController@patchAdminEditWindow',
            'as'   => 'patch.admin.edit.nurse.schedules',
        ]);

        Route::get(
            'athena/ccdas/check',
            'CcdApi\Athena\AthenaApiController@getTodays'
        )->middleware('permission:ccda.create');

        Route::get('athena/ccdas/{practiceId}/{departmentId}', 'CcdApi\Athena\AthenaApiController@fetchCcdas');

        Route::post('calls/import', [
            'uses' => 'CallController@import',
            'as'   => 'post.CallController.import',
        ])->middleware('permission:call.update,call.create');

        Route::get('families/create', [
            'uses' => 'FamilyController@create',
            'as'   => 'family.create',
        ])->middleware('permission:patient.read');

        Route::post('general-comments/import', [
            'uses' => 'UploadsController@postGeneralCommentsCsv',
            'as'   => 'post.GeneralCommentsCsv',
        ])->middleware('permission:patient.update');

        Route::get('calls/{patientId}', 'CallController@showCallsForPatient');

        Route::group([
            'prefix' => 'reports',
        ], function () {
            Route::group([
                'prefix' => 'monthly-billing/v2',
            ], function () {
            /*
             * '/make'
             * '/data'
             * '/counts'
             * '/storeProblem'
             * '/status/update'
             * Search for it above in a different tree of permissions
             */

            Route::get('/services', [
                'uses' => 'Billing\PracticeInvoiceController@getChargeableServices',
                'as'   => 'monthly.billing.services',
            ])->middleware('permission:chargeableService.read');

            Route::post('/updatePracticeServices', [
                'uses' => 'Billing\PracticeInvoiceController@updatePracticeChargeableServices',
                'as'   => 'monthly.billing.practice.services',
            ])->middleware('permission:patientSummary.read,patientSummary.update,patientSummary.create');

            Route::post('/updateSummaryServices', [
                'uses' => 'Billing\PracticeInvoiceController@updateSummaryChargeableServices',
                'as'   => 'monthly.billing.summary.services',
            ])->middleware('permission:patientSummary.read,patientSummary.update,patientSummary.create');

            Route::post('/getBillingCount', [
                'uses' => 'Billing\PracticeInvoiceController@getCounts',
                'as'   => 'monthly.billing.counts',
            ])->middleware('permission:patientSummary.update');

            Route::post('/send', [
                'uses' => 'Billing\PracticeInvoiceController@send',
                'as'   => 'monthly.billing.send',
            ])->middleware('permission:patientSummary.read');
        });

            Route::group([
                'prefix' => 'sales',
            ], function () {
            //LOCATIONS -hidden on adminUI currently.

            Route::get('location/create', [
                'uses' => 'SalesReportsController@createLocationReport',
                'as'   => 'reports.sales.location.create',
            ])->middleware('permission:practice.read');

            Route::post('location/report', [
                'uses' => 'SalesReportsController@makeLocationReport',
                'as'   => 'reports.sales.location.report',
            ])->middleware('permission:salesReport.create');

            //PROVIDERS

            Route::get('provider/create', [
                'uses' => 'SalesReportsController@createProviderReport',
                'as'   => 'reports.sales.provider.create',
            ])->middleware('permission:salesReport.create');

            Route::post('provider/report', [
                'uses' => 'SalesReportsController@makeProviderReport',
                'as'   => 'reports.sales.provider.report',
            ])->middleware('permission:salesReport.create');

            //PRACTICES

            Route::get('practice/create', [
                'uses' => 'SalesReportsController@createPracticeReport',
                'as'   => 'reports.sales.practice.create',
            ])->middleware('permission:salesReport.create');

            Route::post('practice/report', [
                'uses' => 'SalesReportsController@makePracticeReport',
                'as'   => 'reports.sales.practice.report',
            ])->middleware('permission:salesReport.create');
        });

            Route::get('call-v2', [
                'uses' => 'Reports\CallReportController@exportxlsV2',
                'as'   => 'CallReportController.exportxlsv2',
            ])->middleware('permission:call.read,note.read,patient.read,patientSummary.read');

            Route::group([
                'prefix' => 'calls-dashboard',
            ], function () {
            Route::get('/index', [
                'uses' => 'CallsDashboardController@index',
                'as'   => 'CallsDashboard.index',
            ]);

            Route::get('/create', [
                'uses' => 'CallsDashboardController@create',
                'as'   => 'CallsDashboard.create',
            ])->middleware('permission:call.read');

            Route::patch('/edit', [
                'uses' => 'CallsDashboardController@edit',
                'as'   => 'CallsDashboard.edit',
            ])->middleware('permission:call.update');

            Route::post('/create-call', [
                'uses' => 'CallsDashboardController@createCall',
                'as'   => 'CallsDashboard.create-call',
            ])->middleware('permission:call.create');
        });
        });

        Route::group(
            [
                'prefix' => 'report-settings',
            ],
            function () {
                Route::get(
                    '',
                    [
                        'uses' => 'ReportSettingsController@index',
                        'as'   => 'report-settings.index',
                    ]
                );
                Route::post(
                    'update',
                    [
                        'uses' => 'ReportSettingsController@update',
                        'as'   => 'report-settings.update',
                    ]
                );
            }
        );

        Route::group([
            'prefix' => 'settings',
        ], function () {
            Route::group([
                'prefix' => 'manage-cpm-problems',
            ], function () {
            Route::get('/index', [
                'uses' => 'ManageCpmProblemsController@index',
                'as'   => 'manage-cpm-problems.index',
            ])->middleware('permission:patientProblem.read');

            Route::get('/edit', [
                'uses' => 'ManageCpmProblemsController@edit',
                'as'   => 'manage-cpm-problems.edit',
            ])->middleware('permission:patientProblem.read');

            Route::patch('/update', [
                'uses' => 'ManageCpmProblemsController@update',
                'as'   => 'manage-cpm-problems.update',
            ])->middleware('permission:patientProblem.update');
        });
        });

        //Practice Billing
        Route::group(['prefix' => 'practice/billing'], function () {
            Route::get('create', [
                'uses' => 'Billing\PracticeInvoiceController@createInvoices',
                'as'   => 'practice.billing.create',
            ])->middleware('permission:practiceInvoice.read');

            Route::post('make', [
                'uses' => 'Billing\PracticeInvoiceController@makeInvoices',
                'as'   => 'practice.billing.make',
            ])->middleware('permission:practiceInvoice.create');
        });

        // excel reports
        Route::get('excelReportUnreachablePatients', [
            'uses' => 'ReportsController@excelReportUnreachablePatients',
            'as'   => 'excel.report.unreachablePatients',
        ])->middleware('permission:excelReport.create');

        // dashboard
        Route::get('', [
            'uses' => 'DashboardController@index',
            'as'   => 'admin.dashboard',
        ]);
        Route::get('testplan', [
            'uses' => 'DashboardController@testplan',
            'as'   => 'admin.testplan',
        ]);

        Route::get('impersonate/take/{id}', [
            'uses' => '\Lab404\Impersonate\Controllers\ImpersonateController@take',
            'as'   => 'impersonate',
        ]);

        // users
        Route::group([
        ], function () {
            Route::get('calls', [
                'uses' => 'PatientCallManagementController@remix',
                'as'   => 'admin.patientCallManagement.index',
            ]);

            Route::get('time-tracker', [
                'uses' => 'TimeTrackerController@index',
                'as'   => 'admin.timeTracker.index',
            ]);
        });

        // families
        Route::group([
        ], function () {
            Route::get('families', [
                'uses' => 'FamilyController@index',
                'as'   => 'admin.families.index',
            ])->middleware('permission:family.read');
            Route::post('families', [
                'uses' => 'FamilyController@store',
                'as'   => 'admin.families.store',
            ])->middleware('permission:family.create,family.delete');
            Route::get('families/create', [
                'uses' => 'FamilyController@create',
                'as'   => 'admin.families.create',
            ])->middleware('permission:patient.read');
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

        Route::get('reports/nurse/daily', [
            'uses' => 'NurseController@makeDailyReport',
            'as'   => 'admin.reports.nurse.daily',
        ]);

        Route::get('reports/nurse/daily/data', [
            'uses' => 'NurseController@dailyReport',
            'as'   => 'admin.reports.nurse.daily.data',
        ])->middleware('permission:nurseReport.create');

        Route::get('reports/nurse/monthly', [
            'uses' => 'NurseController@monthlyReport',
            'as'   => 'admin.reports.nurse.monthly',
        ])->middleware('permission:nurseReport.create');

        //STATS
        Route::get('reports/nurse/stats', [
            'uses' => 'NurseController@makeHourlyStatistics',
            'as'   => 'stats.nurse.info',
        ]);

        Route::group([
            'prefix' => 'observations-dashboard',
        ], function () {
            Route::get('index', [
                'uses' => 'ObservationController@dashboardIndex',
                'as'   => 'observations-dashboard.index',
            ])->middleware('permission:observation.read');

            Route::get('list', [
                'uses' => 'ObservationController@getObservationsList',
                'as'   => 'observations-dashboard.list',
            ])->middleware('permission:observation.read');

            Route::get('edit', [
                'uses' => 'ObservationController@editObservation',
                'as'   => 'observations-dashboard.edit',
            ])->middleware('permission:observation.read');

            Route::patch('update', [
                'uses' => 'ObservationController@updateObservation',
                'as'   => 'observations-dashboard.update',
            ])->middleware('permission:observation.update');

            Route::delete('delete', [
                'uses' => 'ObservationController@deleteObservation',
                'as'   => 'observations-dashboard.delete',
            ])->middleware('permission:observation.delete');
        });

        // programs
        Route::group([
        ], function () {
            // locations
            Route::get('locations', [
                'uses' => 'LocationController@index',
                'as'   => 'locations.index',
            ])->middleware('permission:location.read');
            Route::get('locations/create', [
                'uses' => 'LocationController@create',
                'as'   => 'locations.create',
            ])->middleware('permission:practice.read');
            Route::post('locations', [
                'uses' => 'LocationController@store',
                'as'   => 'locations.store',
            ])->middleware('permission:location.create');
            Route::get('locations/{id}', [
                'uses' => 'LocationController@show',
                'as'   => 'locations.show',
            ])->middleware('permission:location.read');
            Route::get('locations/{id}/edit', [
                'uses' => 'LocationController@edit',
                'as'   => 'locations.edit',
            ])->middleware('permission:location.read,practice.read');
            Route::post('locations/update', [
                'uses' => 'LocationController@update',
                'as'   => 'locations.update',
            ])->middleware('permission:location.update');
            Route::delete('locations/{id}', [
                'uses' => 'LocationController@destroy',
                'as'   => 'locations.destroy',
            ])->middleware('permission:location.delete');
        });
    });

    Route::group([
        'prefix'     => 'admin',
        'middleware' => [
            'auth',
            'permission:admin-access',
        ],
    ], function () {
        Route::get('/send-enrollee-reminder-test', [
            'uses' => 'Enrollment\AutoEnrollmentTestDashboard@sendEnrolleesReminderTestMethod',
            'as'   => 'send.reminder.enrollee.qa',
        ])->middleware('auth');

        Route::get('/send-patient-reminder-test', [
            'uses' => 'Enrollment\AutoEnrollmentTestDashboard@sendPatientsReminderTestMethod',
            'as'   => 'send.reminder.patient.qa',
        ])->middleware('auth');

        Route::get('/final-action-unreachables-test', [
            'uses' => 'Enrollment\AutoEnrollmentTestDashboard@finalActionTest',
            'as'   => 'final.action.qa',
        ])->middleware('auth');

        Route::get('/evaluate-enrolled-from-survey', [
            'uses' => 'Enrollment\AutoEnrollmentTestDashboard@evaluateEnrolledForSurveyTest',
            'as'   => 'evaluate.survey.completed',
        ])->middleware('auth');

        Route::get('/reset-enrollment-test', [
            'uses' => 'Enrollment\AutoEnrollmentTestDashboard@resetEnrollmentTest',
            'as'   => 'reset.test.qa',
        ])->middleware('auth');

        Route::get('/send-enrollee-invites', [
            'uses' => 'Enrollment\AutoEnrollmentTestDashboard@inviteEnrolleesToEnrollTest',
            'as'   => 'send.enrollee.invitations',
        ])->middleware('auth');

        Route::get('/send-unreachable-invites', [
            'uses' => 'Enrollment\AutoEnrollmentTestDashboard@inviteUnreachablesToEnrollTest',
            'as'   => 'send.unreachable.invitations',
        ])->middleware('auth');

        Route::get('/trigger-enrolldata-test', [
            'uses' => 'Enrollment\AutoEnrollmentTestDashboard@triggerEnrollmentSeederTest',
            'as'   => 'trigger.enrolldata.test',
        ])->middleware('auth');

        Route::get('/invite-unreachable', [
            'uses' => 'Enrollment\AutoEnrollmentTestDashboard@sendInvitesPanelTest',
            'as'   => 'send.invitates.panel',
        ])->middleware('auth');
        //---------------------------------------
    });

    Route::prefix('admin')->group(
        function () {
            Route::prefix('users')->group(
                function () {
                    Route::get(
                        '',
                        [
                            'uses' => 'SuperAdmin\UserController@index',
                            'as'   => 'admin.users.index',
                        ]
                    )->middleware('permission:user.read,practice.read');
                    Route::post(
                        '',
                        [
                            'uses' => 'SuperAdmin\UserController@store',
                            'as'   => 'admin.users.store',
                        ]
                    )->middleware('permission:user.create');
                    Route::get(
                        'create',
                        [
                            'uses' => 'SuperAdmin\UserController@create',
                            'as'   => 'admin.users.create',
                        ]
                    )->middleware('permission:user.read,practice.read,location.read,role.read');
                    Route::get(
                        'doAction',
                        [
                            'uses' => 'SuperAdmin\UserController@doAction',
                            'as'   => 'admin.users.doAction',
                        ]
                    );
                    Route::get(
                        '{id}/edit',
                        [
                            'uses' => 'SuperAdmin\UserController@edit',
                            'as'   => 'admin.users.edit',
                        ]
                    )->middleware('permission:user.read,practice.read,location.read,role.read');
                    Route::get(
                        '{id}/destroy',
                        [
                            'uses' => 'SuperAdmin\UserController@destroy',
                            'as'   => 'admin.users.destroy',
                        ]
                    )->middleware('permission:user.delete');
                    Route::post(
                        '{id}/edit',
                        [
                            'uses' => 'SuperAdmin\UserController@update',
                            'as'   => 'admin.users.update',
                        ]
                    )->middleware('permission:user.update');
                }
            );
        }
    );
    
    Route::group([
        'prefix' => 'ops-dashboard',
    ], function () {
        Route::get('/index', [
            'uses' => 'OpsDashboardController@index',
            'as'   => 'OpsDashboard.index',
        ])->middleware('permission:opsReport.read');
        Route::get('/chart', [
            'uses' => 'OpsDashboardController@opsGraph',
            'as'   => 'OpsDashboard.index.chart',
        ])->middleware('permission:opsReport.read');
        Route::get('/index/csv', [
            'uses' => 'OpsDashboardController@dailyCsv',
            'as'   => 'OpsDashboard.dailyCsv',
        ])->middleware('permission:opsReport.read');
        
        //billing churn - not working, may fix in the future if it becomes a priority
        Route::get('/billing-churn', [
            'uses' => 'OpsDashboardController@getBillingChurn',
            'as'   => 'OpsDashboard.billingChurn',
        ])->middleware('permission:opsReport.read');
    });
});
