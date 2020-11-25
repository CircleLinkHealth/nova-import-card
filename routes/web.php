<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::get('/e/{shortURLKey}', '\AshAllenDesign\ShortURL\Controllers\ShortURLController')->name('short-url.visit');

Route::get('passwordless-login-for-cp-approval/{token}/{patientId}', '\CircleLinkHealth\Customer\Http\Controllers\Auth\LoginController@login')
    ->name('passwordless.login.for.careplan.approval');

Route::post('webhooks/on-sent-fax', [
    'uses' => 'PhaxioWebhookController@onFaxSent',
    'as'   => 'webhook.on-fax-sent',
]);

Route::get('hirefire/{token}/info', 'HireFireController@getQueueSize');

Route::post('send-sample-fax', 'DemoController@sendSampleEfaxNote');

Route::post('/send-sample-direct-mail', 'DemoController@sendSampleEMRNote');

Route::get('care/enroll/{enrollUserId}', 'CareController@enroll');
Route::post('care/enroll/{enrollUserId}', 'CareController@store');

//Algo test routes.

Route::group(['prefix' => 'algo'], function () {
    Route::get('family', 'AlgoTestController@algoFamily');
    
    Route::get('cleaner', 'AlgoTestController@algoCleaner');
    
    Route::get('tuner', 'AlgoTestController@algoTuner');
    
    Route::get('rescheduler', 'AlgoTestController@algoRescheduler');
});

Route::post('account/login', 'Patient\PatientController@patientAjaxSearch');

Route::get('/', 'WelcomeController@index', [
    'as' => 'index',
]);
Route::get('home', [
    'uses' => 'WelcomeController@index',
    'as'   => 'home',
]);

Route::group([
    'prefix'     => 'auth',
    'middleware' => ['web'],
], function () {
    Route::get('enrollment-logout', [
        'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@logoutEnrollee',
        'as'   => 'user.enrollee.logout',
    ]);
});

//
//
//    AUTH ROUTES
//
//
Route::group(['middleware' => 'auth'], function () {
    Route::get('cbt/test-patients/create', [
        'uses' => 'Patient\PatientController@createCBTTestPatient',
        'as'   => 'show.create-test-patients',
    ]);
    
    Route::post('cbt/test-patients', [
        'uses' => 'Patient\PatientController@storeCBTTestPatient',
        'as'   => 'create-test-patients',
    ]);
    
    Route::get('impersonate/leave', [
        'uses' => '\Lab404\Impersonate\Controllers\ImpersonateController@leave',
        'as'   => 'impersonate.leave',
    ]);
    
    Route::get('cache/view/{key}', [
        'as'   => 'get.cached.view.by.key',
        'uses' => 'Cache\UserCacheController@getCachedViewByKey',
    ]);
    
    Route::view('jobs/completed', 'admin.jobsCompleted.manage');
    
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
    
    Route::group([
        'prefix'     => 'ehr-report-writer',
        'middleware' => ['permission:ehr-report-writer-access'],
    ], function () {
        Route::get('index', [
            'uses' => 'EhrReportWriterController@index',
            'as'   => 'report-writer.dashboard',
        ]);
        
        Route::get('download-template/{name}', [
            'uses' => 'EhrReportWriterController@downloadCsvTemplate',
            'as'   => 'report-writer.download-template',
        ]);
        
        Route::post('validate', [
            'uses' => 'EhrReportWriterController@validateJson',
            'as'   => 'report-writer.validate',
        ]);
        
        Route::post('submit', [
            'uses' => 'EhrReportWriterController@submitFile',
            'as'   => 'report-writer.submit',
        ]);
        
        Route::post('notify', [
            'uses' => 'EhrReportWriterController@notifyReportWriter',
            'as'   => 'report-writer.notify',
        ]);
        
        Route::get('google-drive', [
            'uses' => 'EhrReportWriterController@redirectToGoogleDriveFolder',
            'as'   => 'report-writer.google-drive',
        ]);
    });
    
    Route::group([
        'prefix'     => 'patient-user',
        'middleware' => ['auth', 'checkPatientUserData'],
    ], function () {
        Route::get('view-careplan', [
            'uses' => 'PatientUserController@viewCareplan',
            'as'   => 'patient-user.careplan',
        ]);
    });
    
    // API
    Route::group(['prefix' => 'api'], function () {
        Route::get('providers/{providerId}/patients/{patientId}/ccm-time', [
            'uses' => 'API\ActivityController@between',
            'as'   => 'get.ccm.time.from.to',
        ])->middleware('permission:activity.read');
        
        Route::get('patients/{patientId}/ccm-time', [
            'uses' => 'API\ActivityController@ccmTime',
            'as'   => 'get.total.ccm.time',
        ])->middleware('permission:activity.read');
        
        Route::group([
            'prefix'     => 'allergies',
            'middleware' => ['permission:allergy.read'],
        ], function () {
            Route::get('', 'ProblemController@ccdAllergies');
            Route::get('search', 'ProblemController@searchCcdAllergies');
        });
        
        Route::group([
            'prefix'     => 'symptoms',
            'middleware' => ['permission:symptom.read'],
        ], function () {
            Route::get('', 'SymptomController@index');
        });
        
        Route::group([
            'prefix'     => 'lifestyles',
            'middleware' => ['permission:lifestyle.read'],
        ], function () {
            Route::get('{id}', 'LifestyleController@show');
            Route::get('{id}/patients', 'LifestyleController@patients');
            Route::get('', 'LifestyleController@index');
        });
        
        Route::group([
            'prefix'     => 'misc',
            'middleware' => ['permission:misc.read'],
        ], function () {
            Route::get('{id}', 'MiscController@show');
            Route::get('{id}/patients', 'MiscController@patients');
            Route::get('', 'MiscController@index');
        });
        
        Route::group([
            'prefix'     => 'appointments',
            'middleware' => ['permission:appointment.read'],
        ], function () {
            Route::get('{id}', 'API\AppointmentController@show');
            Route::get('', 'API\AppointmentController@index');
        });
        
        Route::group([
            'prefix'     => 'providers',
            'middleware' => ['permission:provider.read'],
        ], function () {
            Route::get('list', 'ProviderController@list');
            Route::get('{id}', 'ProviderController@show');
        });
        
        Route::group([
            'prefix'     => 'locations',
            'middleware' => ['permission:location.read'],
        ], function () {
            Route::get('list', 'ProviderController@listLocations');
        });
        
        Route::group([
            'prefix'     => 'ccda',
            'middleware' => ['permission:ccda.read'],
        ], function () {
            Route::get('{id}', 'CcdaController@show');
            Route::get('', 'CcdaController@index')->middleware('permission:ccda.create');
            Route::post('', 'CcdaController@store')->middleware('permission:ccda.create');
        });
        
        Route::group([
            'prefix'     => 'medication',
            'middleware' => ['permission:medication.read'],
        ], function () {
            Route::get('search', 'MedicationController@search');
            Route::get('', 'MedicationController@index');
            
            Route::group([
                'prefix'     => 'groups',
                'middleware' => ['permission:medication.read'],
            ], function () {
                Route::get('{id}', 'MedicationGroupController@show');
                Route::get('', 'MedicationGroupController@index');
            });
        });
        
        Route::group(['prefix' => 'problems'], function () {
            Route::get('cpm', 'ProblemController@cpmProblems')->middleware('permission:patientProblem.read');
            Route::get('ccd', 'ProblemController@ccdProblems')->middleware('permission:patientProblem.read');
            Route::get('cpm/{cpmId}', 'ProblemController@cpmProblem')->middleware('permission:patientProblem.read');
            Route::get('ccd/{ccdId}', 'ProblemController@ccdProblem')->middleware('permission:patientProblem.read');
            
            Route::group(['prefix' => 'codes'], function () {
                Route::get('', 'ProblemCodeController@index')->middleware('permission:patientProblem.read');
                Route::get('{id}', 'ProblemCodeController@show')->middleware('permission:patientProblem.read');
                Route::delete('{id}', 'ProblemCodeController@remove')->middleware('permission:patientProblem.delete');
                Route::post('', 'ProblemCodeController@store')->middleware('permission:patientProblem.create');
            });
            
            Route::group(['prefix' => 'instructions'], function () {
                Route::get(
                    '{instructionId}',
                    'ProblemInstructionController@instruction'
                )->middleware('permission:instruction.read');
                Route::put('{id}', 'ProblemInstructionController@edit')->middleware('permission:instruction.update');
                Route::get('', 'ProblemInstructionController@index')->middleware('permission:instruction.read');
                Route::post('', 'ProblemInstructionController@store')->middleware('permission:instruction.create');
            });
        });
        
        // ~/api/patients/...
        Route::group([
            'prefix'     => 'patients',
            'middleware' => ['patientProgramSecurity'],
        ], function () {
            
            Route::get('download/{media_id}/{user_id}/{practice_id}', [
                'uses' => 'DownloadController@downloadMediaFromSignedUrl',
                'as'   => 'download.media.from.signed.url',
            ])->middleware('signed');
            
            Route::post(
                '{patientId}/problems/cpm/{cpmId}/instructions',
                'ProblemInstructionController@addInstructionProblem'
            )->middleware('permission:patientProblem.create');
            Route::post(
                '{patientId}/problems/ccd/{problemId}/instructions',
                'ProblemInstructionController@addInstructionToCcdProblem'
            )->middleware('permission:patientProblem.update');
            Route::delete(
                '{patientId}/problems/cpm/{cpmId}/instructions/{instructionId}',
                'ProblemInstructionController@removeInstructionProblem'
            )->middleware('permission:instruction.delete');
            Route::delete(
                '{patientId}/problems/ccd/{problemId}/instructions/{instructionId}',
                'ProblemInstructionController@removeInstructionFromCcdProblem'
            )->middleware('permission:patientProblem.update');
        });
        
        Route::group([
            'middleware' => [
                'permission:ccd-import',
            ],
            'prefix' => 'ccd-importer',
        ], function () {
            Route::get('imported-medical-records', [
                'uses' => 'ImporterController@records',
                'as'   => 'view.records.ready.to.import',
            ]);
            Route::post('imported-medical-records', [
                'uses' => 'ImporterController@uploadRecords',
                'as'   => 'upload.ccda.records',
            ]);
            
            Route::post('records/confirm', [
                'uses' => 'ImporterController@import',
                'as'   => 'imported.records.confirm',
            ]);
            
            Route::get('records/delete', 'ImporterController@deleteRecords');
        });
    });
    
    Route::get(
        'user/{patientId}/care-plan',
        'API\PatientCarePlanController@index'
    )->middleware(['permission:careplan.read']);
    
    Route::get('user/{user}/care-team', [
        'uses' => 'API\CareTeamController@index',
        'as'   => 'user.care-team.index',
    ])->middleware(['permission:carePerson.read']);
    Route::delete('user/{userId}/care-team/{id?}', [
        'uses' => 'API\CareTeamController@destroy',
        'as'   => 'user.care-team.destroy',
    ])->middleware('permission:carePerson.delete');
    Route::patch('user/{userId}/care-team/{id?}', [
        'uses' => 'API\CareTeamController@update',
        'as'   => 'user.care-team.update',
    ])->middleware('permission:carePerson.update');
    Route::get('user/{user}/care-team/{care_team}/edit', [
        'uses' => 'API\CareTeamController@edit',
        'as'   => 'user.care-team.edit',
    ])->middleware(['permission:carePerson.read']);
    
    Route::group(
        [
            'prefix'     => 'enrollment',
            'middleware' => [
                'auth',
                'careAmbassadorAPI',
            ],
        ],
        function () {
            Route::get('/get-suggested-family-members/{enrolleeId}', [
                'uses' => 'API\EnrollmentCenterController@getSuggestedFamilyMembers',
                'as'   => 'enrollment-center.family-members',
            ])->middleware('permission:enrollee.read');
            
            Route::get('queryEnrollable', [
                'uses' => 'API\EnrollmentCenterController@queryEnrollables',
                'as'   => 'enrollables.enrollment.query',
            ]);
            
            Route::get('/show/{enrollableId?}', [
                'uses' => 'API\EnrollmentCenterController@show',
                'as'   => 'enrollment-center.show',
            ])->middleware('permission:enrollee.read');
            
            Route::post('/consented', [
                'uses' => 'API\EnrollmentCenterController@consented',
                'as'   => 'enrollment-center.consented',
            ])->middleware('permission:enrollee.update');
            
            Route::post('/utc', [
                'uses' => 'API\EnrollmentCenterController@unableToContact',
                'as'   => 'enrollment-center.utc',
            ])->middleware('permission:enrollee.update');
            
            Route::post('/rejected', [
                'uses' => 'API\EnrollmentCenterController@rejected',
                'as'   => 'enrollment-center.rejected',
            ])->middleware('permission:enrollee.update');
        }
    );
    
    Route::get('provider/search', [
        'uses' => 'API\CareTeamController@searchProviders',
        'as'   => 'providers.search',
    ])->middleware('permission:provider.read');
    
    Route::delete('pdf/{id}', 'API\PatientCarePlanController@deletePdf')->middleware('permission:careplan-pdf.delete');
    
    Route::post(
        'care-plans/{careplan_id}/pdfs',
        'API\PatientCarePlanController@uploadPdfs'
    )->middleware('permission:careplan.update,careplan-pdf.create');
    
    Route::get('download-pdf-careplan/{filePath}', [
        'uses' => 'API\PatientCarePlanController@downloadPdf',
        'as'   => 'download.pdf.careplan',
    ])->middleware('permission:careplan-pdf.read');
    
    Route::group([
        'middleware' => [],
        'prefix'     => 'patient-email/{patient_id}',
    ], function () {
        Route::post(
            '/upload-attachment',
            'API\PatientEmailController@uploadAttachment'
        );
        
        Route::post(
            '/validate-body',
            [
                'uses' => 'API\PatientEmailController@validateEmailBody',
                'as'   => 'patient-email.validate',
            ]
        );
        
        Route::post(
            '/delete-attachment',
            'API\PatientEmailController@deleteAttachment'
        );
    });
    
    Route::post(
        'care-docs/{patient_id}',
        'API\PatientCareDocumentsController@uploadCareDocuments'
    );
    
    Route::post(
        'send-care-doc/{patient_id}/{media_id}/{channel}/{address_or_fax}',
        'API\PatientCareDocumentsController@sendCareDocument'
    );
    
    Route::get('care-docs/{patient_id}/{show_past?}', [
        'uses' => 'API\PatientCareDocumentsController@getCareDocuments',
        'as'   => 'get.care-docs',
    ]);
    
    Route::get('view-care-document/{patient_id}/{doc_id}', [
        'uses' => 'API\PatientCareDocumentsController@viewCareDocument',
        'as'   => 'view.care-doc',
    ]);
    
    Route::get('download-care-document/{patient_id}/{doc_id}', [
        'uses' => 'API\PatientCareDocumentsController@downloadCareDocument',
        'as'   => 'download.care-doc',
    ]);
    
    Route::patch(
        'work-hours/{id}',
        '\CircleLinkHealth\CpmAdmin\Http\Controllers\CareCenter\WorkScheduleController@updateDailyHours'
    )->middleware('permission:workHours.update');
    // end API
    
    Route::resource(
        'settings/email',
        'EmailSettingsController'
    )->middleware('permission:emailSettings.update,emailSettings.create')->only(['create', 'store']);
    
    Route::get(
        '/CCDModels/Items/MedicationListItem',
        'CCDModels\Items\MedicationListItemController@index'
    )->middleware('permission:medication.read');
    Route::post(
        '/CCDModels/Items/MedicationListItem/store',
        'CCDModels\Items\MedicationListItemController@store'
    )->middleware('permission:medication.create');
    Route::post(
        '/CCDModels/Items/MedicationListItem/update',
        'CCDModels\Items\MedicationListItemController@update'
    )->middleware('permission:medication.update');
    Route::post(
        '/CCDModels/Items/MedicationListItem/destroy',
        'CCDModels\Items\MedicationListItemController@destroy'
    )->middleware('permission:medication.delete');
    
    Route::get(
        '/CCDModels/Items/ProblemsItem',
        'CCDModels\Items\ProblemsItemController@index'
    )->middleware('permission:patientProblem.read');
    Route::post(
        '/CCDModels/Items/ProblemsItem/store',
        'CCDModels\Items\ProblemsItemController@store'
    )->middleware('permission:patientProblem.create');
    Route::post(
        '/CCDModels/Items/ProblemsItem/update',
        'CCDModels\Items\ProblemsItemController@update'
    )->middleware('permission:patientProblem.update');
    Route::post(
        '/CCDModels/Items/ProblemsItem/destroy',
        'CCDModels\Items\ProblemsItemController@destroy'
    )->middleware('permission:patientProblem.delete');
    
    Route::get(
        '/CCDModels/Items/AllergiesItem',
        'CCDModels\Items\AllergiesItemController@index'
    )->middleware('permission:allergy.read');
    Route::post(
        '/CCDModels/Items/AllergiesItem/store',
        'CCDModels\Items\AllergiesItemController@store'
    )->middleware('permission:allergy.create');
    Route::post(
        '/CCDModels/Items/AllergiesItem/update',
        'CCDModels\Items\AllergiesItemController@update'
    )->middleware('permission:allergy.update');
    Route::post(
        '/CCDModels/Items/AllergiesItem/destroy',
        'CCDModels\Items\AllergiesItemController@destroy'
    )->middleware('permission:allergy.delete');
    
    // CCD STUFF
    Route::get('ccd/show/user/{userId}', [
        'uses' => 'CCDViewer\CCDViewerController@showByUserId',
        'as'   => 'get.CCDViewerController.showByUserId',
    ])->middleware('permission:ccda.read');
    
    Route::get('ccd/export/user/{userId}', [
        'uses' => 'CCDViewer\CCDViewerController@exportAllCcds',
        'as'   => 'get.CCDViewerController.exportAllCCDs',
    ])->middleware('permission:ccda.read');
    
    Route::get('ccd/export/user/{userId}', [
        'uses' => 'CCDViewer\CCDViewerController@exportAllCcds',
        'as'   => 'get.CCDViewerController.exportAllCCDs',
    ])->middleware('permission:ccda.read');
    
    Route::get('medical-record/patient/attempt-reimport/{userId}', [
        'uses' => 'ImporterController@reImportPatient',
        'as'   => 'medical-record.patient.reimport',
    ])->middleware('permission:ccda.read');
    
    Route::get('ccd/show/{ccdaId}', [
        'uses' => 'CCDViewer\CCDViewerController@show',
        'as'   => 'get.CCDViewerController.show',
    ])->middleware('permission:ccda.read');
    
    Route::get('ccd/download/xml/{ccdaId}', [
        'uses' => 'CCDViewer\CCDViewerController@downloadXml',
        'as'   => 'download.ccda.xml',
    ])->middleware('permission:ccda.read');
    
    Route::post('ccd', [
        'uses' => 'CCDViewer\CCDViewerController@showUploadedCcd',
        'as'   => 'ccd-viewer.post',
    ])->middleware('permission:ccda.read');
    
    Route::post('ccd/old-viewer', [
        'uses' => 'CCDViewer\CCDViewerController@viewSource',
        'as'   => 'ccd.old.viewer',
    ])->middleware('permission:ccda.read');
    
    Route::get(
        'ccd/old-viewer',
        'CCDViewer\CCDViewerController@create'
    )->middleware('permission:ccda.read')->middleware('permission:ccda.read');
    
    Route::post('ccd-old', [
        'uses' => 'CCDViewer\CCDViewerController@oldViewer',
        'as'   => 'ccd-old-viewer.post',
    ])->middleware('permission:ccda.read');
    
    Route::get(
        '{id}/destroy',
        [
            'uses' => '\CircleLinkHealth\CpmAdmin\Http\Controllers\SuperAdmin\UserController@destroy',
            'as'   => 'admin.users.destroy',
        ]
    )->middleware('permission:user.delete');
    
    // CCD Importer Routes
    Route::group([
        'middleware' => [
            'permission:ccd-import',
        ],
        'prefix' => 'ccd-importer',
    ], function () {
        Route::get('', [
            'uses' => 'ImporterController@remix',
            'as'   => 'import.ccd.remix',
        ]);
        
        Route::post('imported-medical-records', [
            'uses' => 'ImporterController@uploadRawFiles',
            'as'   => 'upload.ccda',
        ]);
    });
    
    //
    // PROVIDER UI (/manage-patients, /reports, ect)
    //
    Route::get('reports/audit/monthly', ['uses' => 'DownloadController@downloadAuditReportsForMonth', 'as' => 'download.monthly.audit.reports'])->middleware('adminOrPracticeStaff');
    Route::get('reports/audit/make', ['uses' => 'DownloadController@makeAuditReportsForMonth', 'as' => 'make.monthly.audit.reports'])->middleware('adminOrPracticeStaff');
    
    // **** PATIENTS (/manage-patients/
    Route::group([
        'prefix'     => 'manage-patients/',
        'middleware' => ['patientProgramSecurity'],
    ], function () {
        Route::group(['prefix' => 'offline-activity-time-requests'], function () {
            Route::get('', [
                'uses' => '\CircleLinkHealth\CpmAdmin\Http\Controllers\OfflineActivityTimeRequestController@index',
                'as'   => 'offline-activity-time-requests.index',
            ])->middleware('permission:patient.read,offlineActivityRequest.read');
        });
        
        Route::get('demographics/create', [
            'uses' => 'Patient\PatientCareplanController@createPatientDemographics',
            'as'   => 'patient.demographics.create',
        ])->middleware('permission:patient.create,patient.update,location.read,practice.read');
        
        Route::post('demographics', [
            'uses' => 'Patient\PatientCareplanController@storePatientDemographics',
            'as'   => 'patient.demographics.store',
        ])->middleware('permission:patient.create,patient.update,careplan.update');
        
        Route::post('delete-phone', [
            'uses' => 'Patient\PatientCareplanController@deletePhoneNumber',
            'as'   => 'patient.phone.delete',
        ])->middleware('permission:phoneNumber.update');

        Route::post('delete-agent-contact', [
            'uses' => 'Patient\PatientCareplanController@deleteAgentContact',
            'as'   => 'patient.agent.contact.delete',
        ])->middleware('permission:patient.update');

        Route::post('get-agent-contact', [
            'uses' => 'Patient\PatientCareplanController@getPatientAgentContact',
            'as'   => 'patient.get.agent.contact',
        ])->middleware('permission:patient.read');

        Route::post('get-phones', [
            'uses' => 'Patient\PatientCareplanController@getPatientPhoneNumbers',
            'as'   => 'patient.get.phones',
        ])->middleware('permission:phoneNumber.create,phoneNumber.update,practice.read');
        
        Route::get('dashboard', [
            'uses' => 'Patient\PatientController@showDashboard',
            'as'   => 'patients.dashboard',
        ])->middleware('permission:patient.read');
        
        Route::get('switch-to-web-careplan/{carePlanId}', [
            'uses' => 'Patient\PatientCareplanController@switchToWebMode',
            'as'   => 'switch.to.web.careplan',
        ])->middleware('permission:careplan.update');
        
        Route::get('switch-to-pdf-careplan/{carePlanId}', [
            'uses' => 'Patient\PatientCareplanController@switchToPdfMode',
            'as'   => 'switch.to.pdf.careplan',
        ])->middleware('permission:careplan.update');
        
        Route::get('listing', [
            'uses' => 'Patient\PatientController@showPatientListing',
            'as'   => 'patients.listing',
        ])->middleware('permission:patient.read');
        
        Route::post('new/phone', [
            'uses' => 'Patient\PatientController@saveNewPhoneNumber',
            'as'   => 'patient.phone.create',
        ])->middleware('permission:phoneNumber.create,phoneNumber.update');

        Route::post('new/agent/phone', [
            'uses' => 'Patient\PatientController@saveNewAgentPhoneNumber',
            'as'   => 'patient.agent.phone.create',
        ])->middleware('permission:patient.create,patient.update,practice.read');
        
        Route::post('mark/primary-phone', [
            'uses' => 'Patient\PatientController@markAsPrimaryPhone',
            'as'   => 'primary.phone.mark',
        ])->middleware('permission:phoneNumber.update');

        Route::get('listing/pdf', [
            'uses' => 'Patient\PatientController@showPatientListingPdf',
            'as'   => 'patients.listing.pdf',
        ])->middleware('permission:careplan-pdf.create');
        
        Route::get('careplan-print-multi', [
            'uses' => 'Patient\PatientCareplanController@printMultiCareplan',
            'as'   => 'patients.careplan.multi',
        ])->middleware('permission:careplan.read,careplan.update,care-plan-approve');
        Route::get('careplan-print-list', [
            'uses' => 'Patient\PatientCareplanController@index',
            'as'   => 'patients.careplan.printlist',
        ])->middleware('permission:careplan.read,patient.read,provider.read');
        Route::post('select', [
            'uses' => 'Patient\PatientController@processPatientSelect',
            'as'   => 'patients.select.process',
        ]);
        Route::get('search', [
            'uses' => 'Patient\PatientController@patientAjaxSearch',
            'as'   => 'patients.search',
        ])->middleware('permission:patient.read');
        Route::get('queryPatient', [
            'uses' => 'Patient\PatientController@queryPatient',
            'as'   => 'patients.query',
        ])->middleware('permission:patient.read');
        Route::get('alerts', [
            'uses' => 'Patient\PatientController@showPatientAlerts',
            'as'   => 'patients.alerts',
        ])->middleware('permission:patient.read');
        Route::get('u20', [
            'uses' => 'ReportsController@u20',
            'as'   => 'patient.reports.u20',
        ])->middleware('permission:patient.read,activity.read');
        Route::get('billing', [
            'uses' => 'ReportsController@billing',
            'as'   => 'patient.reports.billing',
        ])->middleware('permission:patient.read,activity.read');
        Route::get('provider-notes', [
            'uses' => 'NotesController@listing',
            'as'   => 'patient.note.listing',
        ])->middleware('permission:provider.read,note.read');
        
        // nurse call list
        Route::group(['prefix' => 'patient-call-list'], function () {
            Route::get('', [
                'uses' => 'PatientCallListController@index',
                'as'   => 'patientCallList.index',
            ])->middleware('permission:note.read');
        });
    });
    
    Route::group([
        'prefix'     => 'practice/{practiceId}/patient/{patientId}',
        'middleware' => ['patientProgramSecurity'],
    ], function () {
        Route::get('call/schedule', [
            'uses' => 'Patient\PatientController@scheduleActivity',
            'as'   => 'patient.schedule.activity',
        ])->middleware('permission:call.create');

        Route::post('legacy-bhi-consent', [
            'uses' => 'LegacyBhiConsentController@store',
            'as'   => 'legacy-bhi.store',
        ])->middleware('permission:legacy-bhi-consent-decision.create');
    });
    
    Route::post('update-approve-own-care-plans', [
        'uses' => 'ProviderController@updateApproveOwnCarePlan',
        'as'   => 'provider.update-approve-own',
    ])->middleware('permission:care-plan-approve');
    // **** PATIENTS (/manage-patients/{patientId}/)
    Route::group([
        'prefix'     => 'manage-patients/{patientId}',
        'middleware' => ['patientProgramSecurity'],
    ], function () {
        Route::get('call', [
            'uses' => 'Patient\PatientController@showCallPatientPage',
            'as'   => 'patient.show.call.page',
        ])->middleware('permission:patient.read');
        Route::get('summary', [
            'uses' => 'Patient\PatientController@showPatientSummary',
            'as'   => 'patient.summary',
        ])->middleware([
            'permission:patient.read,patientProblem.read,misc.read,observation.read,patientSummary.read',
        ]);
        Route::get('alerts', [
            'uses' => 'Patient\PatientController@showPatientAlerts',
            'as'   => 'patient.alerts',
        ])->middleware('permission:patient.read');
        Route::get('input/observation', [
            'uses' => 'Patient\PatientController@showPatientObservationCreate',
            'as'   => 'patient.observation.create',
        ])->middleware('permission:patient.read,observation.read');
        
        Route::get('view-careplan', [
            'uses' => 'ReportsController@viewPrintCareplan',
            'as'   => 'patient.careplan.print',
        ])->middleware(['permission:careplan.read']);
        
        Route::get('view-careplan/assessment', [
            'uses' => 'ReportsController@makeAssessment',
            'as'   => 'patient.careplan.assessment',
        ])->middleware('permission:careplan.read,careplanAssessment.read');
        
        Route::get('view-careplan/assessment/{approverId}', [
            'uses' => 'ReportsController@makeAssessment',
            'as'   => 'patient.careplan.assessment.approver',
        ])->middleware('permission:careplan.read,careplanAssessment.read');
        
        Route::post('view-careplan/assessment', [
            'uses' => 'CareplanAssessmentController@store',
            'as'   => 'patient.careplan.assessment.create',
        ])->middleware('permission:note.create,careplanAssessment.update');
        
        Route::post('approve-careplan/{viewNext?}', [
            'uses' => 'ProviderController@approveCarePlan',
            'as'   => 'patient.careplan.approve',
        ])->middleware('permission:care-plan-approve,care-plan-qa-approve,care-plan-rn-approve');
        
        Route::post('not-eligible', [
            'uses' => 'ProviderController@removePatient',
            'as'   => 'patient.careplan.not.eligible',
        ])->middleware('permission:care-plan-approve,care-plan-qa-approve,care-plan-rn-approve');
        
        Route::get('view-careplan/pdf', [
            'uses' => 'ReportsController@viewPdfCarePlan',
            'as'   => 'patient.pdf.careplan.print',
        ]);
        
        Route::get('view-care-docs', [
            'uses' => 'ReportsController@viewCareDocumentsPage',
            'as'   => 'patient.care-docs',
        ]);
        
        Route::post('input/observation/create', [
            'uses' => 'ObservationController@store',
            'as'   => 'patient.observation.store',
        ])->middleware('permission:observation.create');
        
        // careplan
        Route::group(['prefix' => 'careplan'], function () {
            Route::get('demographics', [
                'uses' => 'Patient\PatientCareplanController@showPatientDemographics',
                'as'   => 'patient.demographics.show',
            ])->middleware('permission:patient.create,patient.update,location.read,practice.read');
            
            Route::patch('demographics', [
                'uses' => 'Patient\PatientCareplanController@updatePatientDemographics',
                'as'   => 'patient.demographics.update',
            ])->middleware('permission:patient.create,patient.update,careplan.update');
        });
        
        // appointments
        Route::group(['prefix' => 'appointments'], function () {
            Route::get('create', [
                'uses' => 'AppointmentController@create',
                'as'   => 'patient.appointment.create',
            ])->middleware('permission:patient.read,provider.read');
            Route::post('store', [
                'uses' => 'AppointmentController@store',
                'as'   => 'patient.appointment.store',
            ])->middleware('permission:appointment.create');
            Route::get('', [
                'uses' => 'AppointmentController@index',
                'as'   => 'patient.appointment.index',
            ]);
            Route::get('view/{appointmentId}', [
                'uses' => 'AppointmentController@view',
                'as'   => 'patient.appointment.view',
            ])->middleware('permission:appointment.create,patient.read');
        });
        
        Route::group(['prefix' => 'notes'], function () {
            Route::get('create', [
                'uses' => 'NotesController@create',
                'as'   => 'patient.note.create',
            ])->middleware(['permission:note.create']);
            Route::get('edit/{noteId}', [
                'uses' => 'NotesController@create',
                'as'   => 'patient.note.edit',
            ])->middleware(['permission:note.create']);
            Route::post('store', [
                'uses' => 'NotesController@store',
                'as'   => 'patient.note.store',
            ])->middleware('permission:note.create');
            Route::post('store-draft', [
                'uses' => 'NotesController@storeDraft',
                'as'   => 'patient.note.store.draft',
            ])->middleware('permission:note.create');
            Route::post('delete/{noteId}', [
                'uses' => 'NotesController@deleteDraft',
                'as'   => 'patient.note.delete.draft',
            ])->middleware('permission:note.delete');
            Route::get('{showAll?}', [
                'uses' => 'NotesController@index',
                'as'   => 'patient.note.index',
            ])->middleware(['permission:note.read']);
            Route::get('view/{noteId}', [
                'uses' => 'NotesController@show',
                'as'   => 'patient.note.view',
            ])->middleware(['permission:note.read']);
            Route::post('send/{noteId}', [
                'uses' => 'NotesController@send',
                'as'   => 'patient.note.send',
            ])->middleware('permission:note.send');
            Route::post('{noteId}/addendums', [
                'uses' => 'NotesController@storeAddendum',
                'as'   => 'note.store.addendum',
            ])->middleware('permission:addendum.create');
            Route::get('download/{noteId}', [
                'uses' => 'NotesController@download',
                'as'   => 'patient.note.download',
            ])->middleware(['permission:note.download']);
        });
        
        Route::get('progress', [
            'uses' => 'ReportsController@index',
            'as'   => 'patient.reports.progress',
        ])->middleware('permission:patient.read,provider.read,biometric.read,biometric.update,medication.read,medication.update');
        
        Route::group(['prefix' => 'offline-activity-time-requests'], function () {
            Route::get('create', [
                'uses' => '\CircleLinkHealth\CpmAdmin\Http\Controllers\OfflineActivityTimeRequestController@create',
                'as'   => 'offline-activity-time-requests.create',
            ])->middleware('permission:patient.read,offlineActivityRequest.create');
            Route::post('store', [
                'uses' => '\CircleLinkHealth\CpmAdmin\Http\Controllers\OfflineActivityTimeRequestController@store',
                'as'   => 'offline-activity-time-requests.store',
            ])->middleware('permission:offlineActivityRequest.create');
        });
        
        Route::group(['prefix' => 'manual-call', 'middleware' => 'permission:call.create'], function () {
            Route::get('create', [
                'uses' => 'ManualCallController@create',
                'as'   => 'manual.call.create',
            ]);
            Route::post('store', [
                'uses' => 'ManualCallController@store',
                'as'   => 'manual.call.store',
            ]);
        });
        
        Route::get('family-members', [
            'uses' => '\CircleLinkHealth\CpmAdmin\Http\Controllers\FamilyController@getMembers',
            'as'   => 'family.get',
        ])->middleware('permission:patient.read');
    });
});

// pagetimer
Route::group([], function () {
    Route::post('api/v2.1/time/patients', [
        'uses' => 'PageTimerController@getTimeForPatients',
        'as'   => 'api.get.time.patients',
    ]);
    Route::post('api/v2.1/pagetimer', [
        'uses' => 'PageTimerController@store',
        'as'   => 'api.pagetracking',
    ]);
});

// Enrollment Center UI

Route::group([
    'prefix' => '/enrollment',
], function () {
    Route::post('/sms/reply', [
        'uses' => 'Enrollment\EnrollmentSMSController@handleIncoming',
        'as'   => 'enrollment.sms.reply',
    ]);
    
    Route::group([
        'middleware' => [
            'auth',
            'enrollmentCenter',
        ],
    ], function () {
        Route::get('/', [
            'uses' => 'Enrollment\EnrollmentCenterController@dashboard',
            'as'   => 'enrollment-center.dashboard',
        ])->middleware('permission:enrollee.read,enrollee.update');
        
        Route::post('/consented', [
            'uses' => 'API\EnrollmentCenterController@consented',
            'as'   => 'enrollment-center.consented',
        ])->middleware('permission:enrollee.update');
        
        Route::post('/utc', [
            'uses' => 'API\EnrollmentCenterController@unableToContact',
            'as'   => 'enrollment-center.utc',
        ])->middleware('permission:enrollee.update');
        
        Route::post('/rejected', [
            'uses' => 'API\EnrollmentCenterController@rejected',
            'as'   => 'enrollment-center.rejected',
        ])->middleware('permission:enrollee.update');
    });
});

// Enrollment Consent

Route::group([
    'prefix' => 'join',
], function () {
    Route::post('/save', [
        'uses' => 'Enrollment\EnrollmentConsentController@store',
        'as'   => 'patient.enroll.store',
    ])->middleware('permission:enrollee.read,enrollee.update');
    
    Route::get('{invite_code}', [
        'uses' => 'Enrollment\EnrollmentConsentController@create',
        'as'   => 'patient.enroll.create',
    ])->middleware('permission:enrollee.read,enrollee.update');
    
    Route::post('/update', [
        'uses' => 'Enrollment\EnrollmentConsentController@update',
        'as'   => 'patient.enroll.update',
    ])->middleware('permission:enrollee.read,enrollee.update');
});

Route::group([
    'prefix' => 'twilio',
], function () {
    Route::post('/sms/status', [
        'uses' => 'Twilio\TwilioController@smsStatusCallback',
        'as'   => 'twilio.sms.status',
    ]);
    
    Route::post('/sms/inbound', [
        'uses' => 'Twilio\TwilioController@smsInbound',
        'as'   => 'twilio.sms.inbound',
    ]);
});

Route::group([
    'prefix' => 'sendgrid',
], function () {
    Route::post('/status', [
        'uses' => 'SendGridController@statusCallback',
        'as'   => 'sendgrid.status',
    ]);
});

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
    'prefix'     => 'saas/admin',
    'middleware' => ['auth', 'role:saas-admin,administrator,saas-admin-view-only'],
], function () {
    Route::get('home', [
        'uses' => 'Patient\PatientController@showDashboard',
        'as'   => 'saas-admin.home',
    ]);
    
    Route::group(['prefix' => 'users'], function () {
        Route::get('', [
            'uses' => 'SAAS\Admin\CRUD\InternalUserController@index',
            'as'   => 'saas-admin.users.index',
        ]);
        
        Route::get('create', [
            'uses' => 'SAAS\Admin\CRUD\InternalUserController@create',
            'as'   => 'saas-admin.users.create',
        ]);
        
        Route::post('', [
            'uses' => 'SAAS\Admin\CRUD\InternalUserController@store',
            'as'   => 'saas-admin.users.store',
        ]);
        
        Route::get('{userId}', [
            'uses' => 'SAAS\Admin\CRUD\InternalUserController@edit',
            'as'   => 'saas-admin.users.edit',
        ]);
        
        Route::patch('{userId}', [
            'uses' => 'SAAS\Admin\CRUD\InternalUserController@update',
            'as'   => 'saas-admin.users.update',
        ]);
        
        Route::post('action', [
            'uses' => 'SAAS\Admin\CRUD\InternalUserController@action',
            'as'   => 'saas-admin.users.action',
        ]);
    });
    
    Route::resource('practices', 'SAAS\Admin\CRUD\PracticeController', [
        'names' => [
            'index'  => 'saas-admin.practices.index',
            'store'  => 'saas-admin.practices.store',
            'create' => 'saas-admin.practices.create',
            'update' => 'saas-admin.practices.update',
            'show'   => 'saas-admin.practices.show',
            'edit'   => 'saas-admin.practices.edit',
        ],
    ]);
});

Route::get('notifications/{id}', [
    'uses' => 'NotificationController@showPusherNotification',
    'as'   => 'notifications.show',
])->middleware('permission:provider.read,note.read');

Route::get('notifications', [
    'uses' => 'NotificationController@index',
    'as'   => 'notifications.index',
])->middleware('permission:notification.read');

Route::post('/redirect-mark-read/{notificationId}', [
    'uses' => 'NotificationController@markNotificationAsRead',
    'as'   => 'notification.redirect',
]);

Route::get('/redirect-mark-done/{callId}', [
    'uses' => 'PatientCallListController@markAddendumActivitiesDone',
    'as'   => 'redirect.readonly.activity',
]);

Route::get('see-all-notifications', [
    'uses' => 'NotificationController@seeAllNotifications',
    'as'   => 'notifications.seeAll',
])->middleware('permission:provider.read,note.read');

Route::get('all-notifications-pages/{page}/{resultsPerPage}', [
    'uses' => 'NotificationController@seeAllNotificationsPaginated',
    'as'   => 'notifications.all.paginated',
])->middleware('permission:provider.read,note.read');

Route::get('nurses/holidays', [
    'uses' => '\CircleLinkHealth\CpmAdmin\Http\Controllers\CareCenter\WorkScheduleController@getHolidays',
    'as'   => 'get.admin.nurse.schedules.holidays',
])->middleware('permission:nurse.read');

Route::group([
    'prefix'     => 'auth',
    'middleware' => ['web'],
], function () {
    Route::get(
        '/patient-self-enrollment',
        [
            'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@enrollmentAuthForm',
            'as'   => 'invitation.enrollment.loginForm',
        ]
    )->middleware('signed');
    
    Route::post('login-enrollment-survey', [
        'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@authenticate',
        'as'   => 'invitation.enrollment.login',
    ]);
});
// TEMPORARY SIGNED ROUTE

Route::get('/enrollment-survey', [
    'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@enrollNow',
    'as'   => 'patient.self.enroll.now',
]);

Route::get('/enrollment-info', [
    'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@enrolleeRequestsInfo',
    'as'   => 'patient.requests.enroll.info',
]);

// Redirects to view with enrollees details to contact.
Route::get('/enrollee-contact-details', [
    'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@enrolleeContactDetails',
    'as'   => 'enrollee.to.call.details',
])->middleware('auth');

// Incoming from AWV
Route::get('/review-letter/{userId}', [
    'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@reviewLetter',
    'as'   => 'enrollee.to.review.letter',
]);

Route::get('/notification-unsubscribe', [
    'uses' => 'NotificationsMailSubscriptionController@unsubscribe',
    'as'   => 'unsubscribe.notifications.mail',
])->middleware('signed', 'auth');

Route::post('/update-subscriptions', [
    'uses' => 'SubscriptionsDashboardController@updateSubscriptions',
    'as'   => 'update.subscriptions',
])->middleware('auth');

Route::get('/notification-subscriptions-dashboard', [
    'uses' => 'SubscriptionsDashboardController@subscriptionsIndex',
    'as'   => 'subscriptions.notification.mail',
])->middleware('auth');

Route::post('nurses/nurse-calendar-data', [
    'uses' => '\CircleLinkHealth\CpmAdmin\Http\Controllers\CareCenter\WorkScheduleController@getSelectedNurseCalendarData',
    'as'   => 'get.nurse.schedules.selectedNurseCalendar',
])->middleware('permission:nurse.read');

Route::get('login-enrollees-survey/{user}/{survey}', '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@sendToSurvey')
    ->name('enrollee.login.signed')
    ->middleware('signed');

Route::post('enrollee-login-viewed', [
    'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@viewFormVisited',
    'as'   => 'enrollee.login.viewed',
])->middleware('guest');
