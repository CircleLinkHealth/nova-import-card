<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::get('env', [
    'uses' => 'EnvController@getEnv',
    'as'   => 'welcome.get.env',
])->middleware('auth');

Route::get('login', [
    'uses' => 'Auth\LoginController@showLoginForm',
    'as'   => 'login',
]);
Route::get('auth/login', [
    'uses' => 'Auth\LoginController@showLoginForm',
    'as'   => 'auth.login',
]);
Route::post('login', 'Auth\LoginController@login');
Route::post('auth/login', 'Auth\LoginController@login');
Route::post('browser-check', [
    'uses' => 'Auth\LoginController@storeBrowserCompatibilityCheckPreference',
    'as'   => 'store.browser.compatibility.check.preference',
]);

Route::group([
    'prefix'     => 'auth',
    'middleware' => ['web'],
], function () {
    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
    Route::get('password/confirm', 'Auth\ConfirmPasswordController@showConfirmForm')->name('password.confirm');
    Route::post('password/confirm', 'Auth\ConfirmPasswordController@confirm');

    Route::get('logout', [
        'uses' => 'Auth\LoginController@logout',
        'as'   => 'user.logout',
    ]);
    Route::get('inactivity-logout', [
        'uses' => 'Auth\LoginController@inactivityLogout',
        'as'   => 'user.inactivity-logout',
    ]);
});

Route::group([
    'middleware' => ['web', 'auth'],
], function () {
    Route::get('reports/audit/monthly', ['uses' => 'DownloadController@downloadAuditReportsForMonth', 'as' => 'download.monthly.audit.reports'])->middleware('adminOrPracticeStaff');
    Route::get('reports/audit/make', ['uses' => 'DownloadController@makeAuditReportsForMonth', 'as' => 'make.monthly.audit.reports'])->middleware('adminOrPracticeStaff');

    Route::get('download/{filePath}', [
        'uses' => 'DownloadController@file',
        'as'   => 'download',
    ]);
    Route::get('download-media-collection-zip/{collectionName}', [
        'uses' => 'DownloadController@downloadUserMediaCollectionAsZip',
        'as'   => 'download.collection-as-zip',
    ]);

    Route::get('download-google-drive-csv/{filename}/{dir?}/{recursive?}', [
        'uses' => 'DownloadController@downloadCsvFromGoogleDrive',
        'as'   => 'download.google.csv',
    ]);

    Route::get('download-zipped-media/{user_id}/{media_ids}', [
        'uses' => 'DownloadController@downloadZippedMedia',
        'as'   => 'download.zipped.media',
    ])->middleware('signed');

    Route::get('download/{media_id}/{user_id}/{practice_id}', [
        'uses' => 'DownloadController@downloadMediaFromSignedUrl',
        'as'   => 'download.media.from.signed.url',
    ])->middleware('signed');

    Route::get('sentrydemo', 'SentryDemoController@throw');
    Route::group(['prefix' => 'calls'], function () {
        Route::get('', [
            'uses' => 'CallController@index',
            'as'   => 'call.index',
        ])->middleware('permission:call.read');
        Route::get('create', [
            'uses' => 'CallController@create',
            'as'   => 'call.create',
        ])->middleware('permission:call.create');
        Route::get('edit/{actId}', [
            'uses' => 'CallController@edit',
            'as'   => 'call.edit',
        ]);
        Route::post('reschedule/{patientId}', [
            'uses' => 'CallController@reschedule',
            'as'   => 'call.reschedule',
        ])->middleware('permission:call.update');
    });

    Route::group([
        'prefix' => 'reports',
    ], function () {
        Route::group([
            'prefix' => 'monthly-billing/v2',
        ], function () {
            Route::post('/updatePracticeServices', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@updatePracticeChargeableServices',
                'as'   => 'monthly.billing.practice.services',
            ])->middleware('permission:patientSummary.read,patientSummary.update,patientSummary.create');

            Route::get('/make', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@make',
                'as'   => 'monthly.billing.make',
            ])->middleware('permission:patientSummary.read,patientProblem.read,chargeableService.read,practice.read');

            Route::post('/data', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@data',
                'as'   => 'monthly.billing.data',
            ])->middleware('permission:patientSummary.read,patientSummary.update,patientSummary.create');

            Route::get('/counts', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@counts',
            ])->middleware('permission:patientSummary.read');

            Route::post('/close', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@closeMonthlySummaryStatus',
                'as'   => 'monthly.billing.close.month',
            ])->middleware('permission:patientSummary.update');

            Route::post('/open', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@openMonthlySummaryStatus',
                'as'   => 'monthly.billing.open.month',
            ])->middleware('permission:patientSummary.update');

            Route::post('/updateSummaryServices', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@updateSummaryChargeableServices',
                'as'   => 'monthly.billing.updateSummaryServices',
            ])->middleware('permission:patientSummary.update');

            Route::post('/status/update', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@updateStatus',
                'as'   => 'monthly.billing.status.update',
            ])->middleware('permission:patientSummary.update');
        });
    });

    Route::group(['prefix' => 'practice/billing'], function () {
        Route::get('create', [
            'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@createInvoices',
            'as'   => 'practice.billing.create',
        ])->middleware('permission:practiceInvoice.read');

        Route::post('make', [
            'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@makeInvoices',
            'as'   => 'practice.billing.make',
        ])->middleware('permission:practiceInvoice.create');
    });

    Route::group(['prefix' => 'monthly-billing'], function () {
        Route::get('make', [
            'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@make',
            'as'   => 'saas-admin.monthly.billing.make',
        ]);

        Route::post('data', [
            'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@data',
            'as'   => 'saas-admin.monthly.billing.data',
        ]);
    });

    Route::group(['prefix' => 'practice/billing'], function () {
        Route::get('create', [
            'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@createInvoices',
            'as'   => 'saas-admin.practices.billing.create',
        ]);
    });

    Route::group([
        'middleware' => ['permission:has-schedule'],
        'prefix'     => 'care-center',
    ], function () {
        Route::resource('work-schedule', 'CareCenter\WorkScheduleController', [
            'only' => [
                'index',
                'store',
            ],
            'names' => [
                'index' => 'care.center.work.schedule.index',
                'store' => 'care.center.work.schedule.store',
            ],
        ])->middleware('permission:nurseContactWindow.read,nurseContactWindow.create');

        Route::get('work-schedule/get-calendar-data', [
            'uses' => 'CareCenter\WorkScheduleController@calendarEvents',
            'as'   => 'care.center.work.schedule.getCalendarData',
        ])->middleware('permission:nurseContactWindow.read');

        Route::get('work-schedule/get-daily-report', [
            'uses' => 'CareCenter\WorkScheduleController@dailyReportsForNurse',
            'as'   => 'care.center.work.schedule.getDailyReport',
        ])->middleware('permission:nurseContactWindow.read');

        Route::get('work-schedule/get-nurse-calendar-data', [
            'uses' => 'CareCenter\WorkScheduleController@calendarWorkEventsForAuthNurse',
            'as'   => 'care.center.work.schedule.calendarWorkEventsForAuthNurse',
        ])->middleware('permission:nurseContactWindow.read');

        Route::get('work-schedule/destroy/{id}', [
            'uses' => 'CareCenter\WorkScheduleController@destroy',
            'as'   => 'care.center.work.schedule.destroy',
        ])->middleware('permission:nurseContactWindow.delete');

        Route::post('work-schedule/holidays', [
            'uses' => 'CareCenter\WorkScheduleController@storeHoliday',
            'as'   => 'care.center.work.schedule.holiday.store',
        ])->middleware('permission:nurseHoliday.create');

        Route::get('work-schedule/holidays/destroy/{id}', [
            'uses' => 'CareCenter\WorkScheduleController@destroyHoliday',
            'as'   => 'care.center.work.schedule.holiday.destroy',
        ])->middleware('permission:nurseHoliday.delete');
    });

    Route::group([
        'prefix'     => 'manage-patients',
        'middleware' => ['patientProgramSecurity'],
    ], function () {
        Route::group([
            'prefix' => '{patientId}',
        ], function () {
            Route::get('family-members', [
                'uses' => 'FamilyController@getMembers',
                'as'   => 'family.get',
            ])->middleware('permission:patient.read');

            Route::get('calls/next', [
                'uses' => 'CallController@getPatientNextScheduledCallJson',
                'as'   => 'call.next',
            ])->middleware('permission:call.read');
        });

        Route::group(['prefix' => 'offline-activity-time-requests'], function () {
            Route::get('care-coach', [
                'uses' => 'OfflineActivityTimeRequestController@index',
                'as'   => 'offline-activity-time-requests.index',
            ])->middleware('permission:patient.read,offlineActivityRequest.read');

            Route::get('admin', [
                'uses' => 'OfflineActivityTimeRequestController@adminIndex',
                'as'   => 'admin.offline-activity-time-requests.index',
            ])->middleware('permission:patient.read,offlineActivityRequest.read');
            Route::post('respond', [
                'uses' => 'OfflineActivityTimeRequestController@adminRespond',
                'as'   => 'admin.offline-activity-time-requests.respond',
            ])->middleware('permission:patient.read,offlineActivityRequest.read');
            Route::get('create/{patientId}', [
                'uses' => 'OfflineActivityTimeRequestController@create',
                'as'   => 'offline-activity-time-requests.create',
            ])->middleware('permission:patient.read,offlineActivityRequest.create');
            Route::post('store/{patientId}', [
                'uses' => 'OfflineActivityTimeRequestController@store',
                'as'   => 'offline-activity-time-requests.store',
            ])->middleware('permission:offlineActivityRequest.create');
        });
    });

    Route::post('nurses/nurse-calendar-data', [
        'uses' => 'CareCenter\WorkScheduleController@getSelectedNurseCalendarData',
        'as'   => 'get.nurse.schedules.selectedNurseCalendar',
    ])->middleware('permission:nurse.read');
});

//This route was replaced by route with url '/downloadInvoice/{practice}/{name}', and name 'monthly.billing.download'.
//We keep it here to support Report links mailed before 5/12/17.
Route::get('/admin/reports/monthly-billing/v2/downloadInvoice/{practice}/{name}', [
    'uses'       => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@downloadInvoice',
    'middleware' => ['auth'],
]);

Route::get('/downloadInvoice/{practice}/{name}', [
    'uses'       => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@downloadInvoice',
    'as'         => 'monthly.billing.download',
    'middleware' => ['auth'],
]);

Route::post('forward-careplan-to-billing-provider-via-dm', [
    'uses' => 'Patient\CarePlanController@forwardToBillingProviderViaDM',
    'as'   => 'forward-careplan-to-billing-provider-via-dm',
])->middleware(['patientProgramSecurity']);

Route::prefix('api')->group(function () {
    Route::group(['prefix' => 'practices'], function () {
        Route::get('', 'API\PracticeController@getPractices')->middleware('permission:practice.read');
        Route::get(
            '{practiceId}/providers',
            'API\PracticeController@getPracticeProviders'
        )->middleware('permission:provider.read');
        Route::get(
            '{practiceId}/locations',
            'API\PracticeController@getPracticeLocations'
        )->middleware('permission:location.read');
        Route::get(
            '{practiceId}/locations/{locationId}/providers',
            [
                'uses' => 'API\PracticeController@getLocationProviders',
                'as'   => 'api.get.location.providers',
            ]
        )->middleware('permission:provider.read');
        Route::get(
            'all',
            'API\PracticeController@allPracticesWithLocationsAndStaff'
        )->middleware('permission:practice.read,location.read,provider.read');
        Route::get(
            '{practiceId}/patients',
            'API\PracticeController@getPatients'
        )->middleware('permission:patient.read');
        Route::get('{practiceId}/nurses', 'API\PracticeController@getNurses')->middleware('permission:nurse.read');
    });
});

Route::get('impersonate/take/{id}', [
    'uses' => '\Lab404\Impersonate\Controllers\ImpersonateController@take',
    'as'   => 'impersonate',
])->middleware(['auth',
    'permission:admin-access', ]);

Route::group([
    'prefix' => 'postmark',
], function () {
    Route::post('/status', [
        'uses' => 'Postmark\PostmarkController@statusCallback',
        'as'   => 'postmark.status',
    ]);

    Route::post('/inbound', [
        'uses' => 'Postmark\PostmarkController@inbound',
        'as'   => 'postmark.inbound',
    ]);
});

Route::group([
    'prefix'     => 'practices/{practiceSlug}',
    'middleware' => [
        'auth',
        'providerDashboardACL:administrator,saas-admin,saas-admin-view-only',
    ],
], function () {
    Route::post('chargeable-services', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@postStoreChargeableServices',
        'as'   => 'provider.dashboard.store.chargeable-services',
    ])->middleware('permission:practiceSetting.create');

    Route::get('chargeable-services', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@getCreateChargeableServices',
        'as'   => 'provider.dashboard.manage.chargeable-services',
    ])->middleware('permission:practiceSetting.read');

    Route::post('invite', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@postStoreInvite',
        'as'   => 'post.store.invite',
    ])->middleware('permission:invite.create');

    Route::post('locations', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@postStoreLocations',
        'as'   => 'provider.dashboard.store.locations',
    ])->middleware('permission:practiceSetting.create');

    Route::post('staff', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@postStoreStaff',
        'as'   => 'provider.dashboard.store.staff',
    ])->middleware('permission:practiceSetting.update');

    Route::post('notifications', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@postStoreNotifications',
        'as'   => 'provider.dashboard.store.notifications',
    ])->middleware('permission:practiceSetting.update');

    Route::get('notifications', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@getCreateNotifications',
        'as'   => 'provider.dashboard.manage.notifications',
    ])->middleware('permission:practiceSetting.read');

    Route::post('practice', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@postStorePractice',
        'as'   => 'provider.dashboard.store.practice',
    ])->middleware('permission:practiceSetting.update');

    Route::get('practice', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@getCreatePractice',
        'as'   => 'provider.dashboard.manage.practice',
    ])->middleware('permission:practiceSetting.read');

    Route::get('staff', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@getCreateStaff',
        'as'   => 'provider.dashboard.manage.staff',
    ])->middleware('permission:practiceSetting.read');

    Route::get('locations', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@getCreateLocation',
        'as'   => 'provider.dashboard.manage.locations',
    ])->middleware('permission:practiceSetting.read');

    Route::get('enrollment', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@getCreateEnrollment',
        'as'   => 'provider.dashboard.manage.enrollment',
    ])->middleware('permission:practiceSetting.read');

    Route::post('enrollment', [
        'uses' => '\CircleLinkHealth\Customer\PracticeSettings\Http\Controllers\DashboardController@postStoreEnrollment',
        'as'   => 'provider.dashboard.store.enrollment',
    ])->middleware('permission:practiceSetting.update');
});
