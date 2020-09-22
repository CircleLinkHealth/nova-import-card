<?php

Route::get('login', [
    'uses' => 'Auth\LoginController@showLoginForm',
    'as' => 'login'
]);
Route::get('auth/login', [
    'uses' => 'Auth\LoginController@showLoginForm',
    'as' => 'auth.login'
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
    'prefix'     => 'auth',
    'middleware' => ['web'],
], function () {
    Route::group([
        'prefix' => 'reports',
    ], function () {
        Route::group([
            'prefix' => 'monthly-billing/v2',
        ], function () {
            Route::get('/make', [
                'uses' => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@make',
                'as'   => 'monthly.billing.make',
            ])->middleware('permission:patientSummary.read,patientProblem.read,chargeableService.read,practice.read');
            
            Route::post('/data', [
                'uses' => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@data',
                'as'   => 'monthly.billing.data',
            ])->middleware('permission:patientSummary.read,patientSummary.update,patientSummary.create');
            
            Route::get('/counts', [
                'uses' => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@counts',
            ])->middleware('permission:patientSummary.read');
            
            Route::post('/close', [
                'uses' => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@closeMonthlySummaryStatus',
                'as'   => 'monthly.billing.close.month',
            ])->middleware('permission:patientSummary.update');
            
            Route::post('/open', [
                'uses' => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@openMonthlySummaryStatus',
                'as'   => 'monthly.billing.open.month',
            ])->middleware('permission:patientSummary.update');
            
            Route::post('/status/update', [
                'uses' => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@updateStatus',
                'as'   => 'monthly.billing.status.update',
            ])->middleware('permission:patientSummary.update');
        });
    });
    
    Route::group(['prefix' => 'practice/billing'], function () {
        Route::get('create', [
            'uses' => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@createInvoices',
            'as'   => 'practice.billing.create',
        ])->middleware('permission:practiceInvoice.read');
        
        Route::post('make', [
            'uses' => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@makeInvoices',
            'as'   => 'practice.billing.make',
        ])->middleware('permission:practiceInvoice.create');
    });
    
    Route::group(['prefix' => 'monthly-billing'], function () {
        Route::get('make', [
            'uses' => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@make',
            'as'   => 'saas-admin.monthly.billing.make',
        ]);
        
        Route::post('data', [
            'uses' => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@data',
            'as'   => 'saas-admin.monthly.billing.data',
        ]);
    });
    
    Route::group(['prefix' => 'practice/billing'], function () {
        Route::get('create', [
            'uses' => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@createInvoices',
            'as'   => 'saas-admin.practices.billing.create',
        ]);
    });
    
    Route::group([
        'middleware' => ['permission:has-schedule'],
        'prefix'     => 'care-center',
    ], function () {
        Route::resource('work-schedule', '\CircleLinkHealth\Customer\Http\Controllers\CareCenter\WorkScheduleController', [
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
            'uses' => '\CircleLinkHealth\Customer\Http\Controllers\CareCenter\WorkScheduleController@calendarEvents',
            'as'   => 'care.center.work.schedule.getCalendarData',
        ])->middleware('permission:nurseContactWindow.read');
        
        Route::get('work-schedule/get-daily-report', [
            'uses' => '\CircleLinkHealth\Customer\Http\Controllers\CareCenter\WorkScheduleController@dailyReportsForNurse',
            'as'   => 'care.center.work.schedule.getDailyReport',
        ])->middleware('permission:nurseContactWindow.read');
        
        Route::get('work-schedule/get-nurse-calendar-data', [
            'uses' => '\CircleLinkHealth\Customer\Http\Controllers\CareCenter\WorkScheduleController@calendarWorkEventsForAuthNurse',
            'as'   => 'care.center.work.schedule.calendarWorkEventsForAuthNurse',
        ])->middleware('permission:nurseContactWindow.read');
        
        Route::get('work-schedule/destroy/{id}', [
            'uses' => '\CircleLinkHealth\Customer\Http\Controllers\CareCenter\WorkScheduleController@destroy',
            'as'   => 'care.center.work.schedule.destroy',
        ])->middleware('permission:nurseContactWindow.delete');
        
        Route::post('work-schedule/holidays', [
            'uses' => '\CircleLinkHealth\Customer\Http\Controllers\CareCenter\WorkScheduleController@storeHoliday',
            'as'   => 'care.center.work.schedule.holiday.store',
        ])->middleware('permission:nurseHoliday.create');
        
        Route::get('work-schedule/holidays/destroy/{id}', [
            'uses' => '\CircleLinkHealth\Customer\Http\Controllers\CareCenter\WorkScheduleController@destroyHoliday',
            'as'   => 'care.center.work.schedule.holiday.destroy',
        ])->middleware('permission:nurseHoliday.delete');
    });
});

//This route was replaced by route with url '/downloadInvoice/{practice}/{name}', and name 'monthly.billing.download'.
//We keep it here to support Report links mailed before 5/12/17.
Route::get('/admin/reports/monthly-billing/v2/downloadInvoice/{practice}/{name}', [
    'uses'       => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@downloadInvoice',
    'middleware' => ['auth'],
]);

Route::get('/downloadInvoice/{practice}/{name}', [
    'uses'       => '\CircleLinkHealth\Customer\Billing\Http\Controllers\Billing\PracticeInvoiceController@downloadInvoice',
    'as'         => 'monthly.billing.download',
    'middleware' => ['auth'],
]);