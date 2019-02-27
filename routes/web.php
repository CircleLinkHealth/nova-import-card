<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::post('send-sample-fax', 'DemoController@sendSampleEfaxNote');

Route::post('/send-sample-direct-mail', 'DemoController@sendSampleEMRNote');

//Patient Landing Pages
Route::resource('sign-up', 'PatientSignupController');
Route::get('talk-to-us', 'PatientSignupController@talkToUs');

Route::get('care/enroll/{enrollUserId}', 'CareController@enroll');
Route::post('care/enroll/{enrollUserId}', 'CareController@store');

//Algo test routes.

Route::group(['prefix' => 'algo'], function () {
    Route::get('family', 'AlgoTestController@algoFamily');

    Route::get('cleaner', 'AlgoTestController@algoCleaner');

    Route::get('tuner', 'AlgoTestController@algoTuner');

    Route::get('rescheduler', 'AlgoTestController@algoRescheduler');
});

Route::get('ajax/patients', 'UserController@getPatients');

Route::post('account/login', 'Patient\PatientController@patientAjaxSearch');

Route::get('/', 'WelcomeController@index', [
    'as' => 'index',
]);
Route::get('home', 'WelcomeController@index', [
    'as' => 'home',
]);

Route::get('login', 'Auth\LoginController@showLoginForm');
Route::post('browser-check', [
    'uses' => 'Auth\LoginController@storeBrowserCompatibilityCheckPreference',
    'as'   => 'store.browser.compatibility.check.preference',
]);

Route::group([
    'prefix'     => 'auth',
    'middleware' => 'web',
], function () {
    Auth::routes();

    Route::get('logout', [
        'uses' => 'Auth\LoginController@logout',
        'as'   => 'user.logout',
    ]);
    Route::get('inactivity-logout', [
        'uses' => 'Auth\LoginController@inactivityLogout',
        'as'   => 'user.inactivity-logout',
    ]);
});

//
//
//    AUTH ROUTES
//
//
Route::group(['middleware' => 'auth'], function () {
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

    Route::get('download', [
        'uses' => 'DownloadController@postDownloadfile',
        'as'   => 'post.file.download',
    ]);

    Route::group(['prefix' => 'ehr-report-writer'], function () {
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
    });

    Route::group(['prefix' => '2fa'], function () {
        Route::get('', [
            'uses' => 'AuthyController@showVerificationTokenForm',
            'as'   => 'user.2fa.show.token.form',
        ]);
    });

    // API
    Route::group(['prefix' => 'api'], function () {
        Route::group(['prefix' => '2fa'], function () {
            Route::group(['prefix' => 'token'], function () {
                Route::post('sms', [
                    'uses' => 'AuthyController@sendTokenViaSms',
                    'as'   => 'user.2fa.token.sms',
                ]);

                Route::post('voice', [
                    'uses' => 'AuthyController@sendTokenViaVoice',
                    'as'   => 'user.2fa.token.voice',
                ]);

                Route::post('verify', [
                    'uses' => 'AuthyController@verifyToken',
                    'as'   => 'user.2fa.token.verify',
                ]);
            });
            Route::group(['prefix' => 'one-touch-request'], function () {
                Route::post('create', [
                    'uses' => 'AuthyController@createOneTouchRequest',
                    'as'   => 'user.2fa.one-touch-request.create',
                ]);

                Route::post('check-status', [
                    'uses' => 'AuthyController@checkOneTouchRequestStatus',
                    'as'   => 'user.2fa.one-touch-request.check',
                ]);
            });
        });

        Route::group(['prefix' => 'account-settings'], function () {
            Route::group(['prefix' => '2fa'], function () {
                Route::post('', [
                    'uses' => 'AuthyController@store',
                    'as'   => 'user.2fa.store',
                ]);
            });
        });

        Route::group(['prefix' => 'admin'], function () {
            //the new calls route that uses calls-view table
            Route::get('calls-v2', [
                'uses' => 'API\Admin\CallsViewController@index',
                'as'   => 'calls.v2.index',
            ])->middleware('permission:call.read');

            Route::group(['prefix' => 'calls'], function () {
                Route::get('', [
                    'uses' => 'API\Admin\CallsController@index',
                    'as'   => 'calls.index',
                ])->middleware('permission:call.read');

                Route::get('{id}', [
                    'uses' => 'API\Admin\CallsController@show',
                    'as'   => 'calls.show',
                ])->middleware('permission:call.read');

                Route::delete('{ids}', [
                    'uses' => 'API\Admin\CallsController@remove',
                    'as'   => 'calls.destroy',
                ])->middleware('permission:call.delete');
            });

            Route::resource(
                'user.outbound-calls',
                'API\UserOutboundCallController'
            )->middleware('permission:call.create');
        });

        Route::get('providers/{providerId}/patients/{patientId}/ccm-time', [
            'uses' => 'API\ActivityController@between',
            'as'   => 'get.ccm.time.from.to',
        ])->middleware('permission:activity.read');

        Route::get('patients/{patientId}/ccm-time', [
            'uses' => 'API\ActivityController@ccmTime',
            'as'   => 'get.total.ccm.time',
        ])->middleware('permission:activity.read');

        Route::group([
            'prefix'     => 'biometrics',
            'middleware' => ['permission:biometric.read'],
        ], function () {
            Route::get('', 'BiometricController@index');
            Route::get('{biometricId}', 'BiometricController@show');
            Route::get('{biometricId}/patients', 'BiometricController@patients');
        });

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
            Route::resource('', 'SymptomController');
        });

        Route::group([
            'prefix'     => 'lifestyles',
            'middleware' => ['permission:lifestyle.read'],
        ], function () {
            Route::get('{id}', 'LifestyleController@show');
            Route::get('{id}/patients', 'LifestyleController@patients');
            Route::resource('', 'LifestyleController');
        });

        Route::group([
            'prefix'     => 'misc',
            'middleware' => ['permission:misc.read'],
        ], function () {
            Route::get('{id}', 'MiscController@show');
            Route::get('{id}/patients', 'MiscController@patients');
            Route::resource('', 'MiscController');
        });

        Route::get('test', 'MiscController@test');

        Route::group([
            'prefix'     => 'appointments',
            'middleware' => ['permission:appointment.read'],
        ], function () {
            Route::get('{id}', 'API\AppointmentController@show');
            Route::resource('', 'API\AppointmentController');
        });

        Route::group([
            'prefix'     => 'providers',
            'middleware' => ['permission:provider.read'],
        ], function () {
            Route::get('list', 'ProviderController@list');
            Route::get('{id}', 'ProviderController@show');
            Route::resource('', 'ProviderController');
        });

        Route::group([
            'prefix'     => 'ccda',
            'middleware' => ['permission:ccda.read'],
        ], function () {
            Route::get('{id}', 'CcdaController@show');
            Route::resource('', 'CcdaController')->middleware('permission:ccda.create');
        });

        Route::group([
            'prefix'     => 'medication',
            'middleware' => ['permission:medication.read'],
        ], function () {
            Route::get('search', 'MedicationController@search');
            Route::resource('', 'MedicationController');

            Route::group([
                'prefix'     => 'groups',
                'middleware' => ['permission:medication.read'],
            ], function () {
                Route::get('{id}', 'MedicationGroupController@show');
                Route::resource('', 'MedicationGroupController');
            });
        });

        Route::group(['prefix' => 'problems'], function () {
            Route::get('cpm', 'ProblemController@cpmProblems')->middleware('permission:patientProblem.read');
            Route::get('ccd', 'ProblemController@ccdProblems')->middleware('permission:patientProblem.read');
            Route::get('cpm/{cpmId}', 'ProblemController@cpmProblem')->middleware('permission:patientProblem.read');
            Route::get('ccd/{ccdId}', 'ProblemController@ccdProblem')->middleware('permission:patientProblem.read');
            Route::resource('', 'ProblemController')->middleware('permission:patientProblem.read');

            Route::group(['prefix' => 'codes'], function () {
                Route::get('{id}', 'ProblemCodeController@show')->middleware('permission:patientProblem.read');
                Route::delete('{id}', 'ProblemCodeController@remove')->middleware('permission:patientProblem.delete');
                Route::resource(
                    '',
                    'ProblemCodeController'
                )->middleware('permission:patientProblem.read,patientProblem.create,patientProblem.delete');
            });

            Route::group(['prefix' => 'instructions'], function () {
                Route::get('search', 'ProblemInstructionController@search');
                Route::get(
                    '{instructionId}',
                    'ProblemInstructionController@instruction'
                )->middleware('permission:instruction.read');
                Route::put('{id}', 'ProblemInstructionController@edit')->middleware('permission:instruction.update');
                Route::resource('', 'ProblemInstructionController');
            });
        });

        // ~/api/patients/...
        Route::group([
            'prefix'     => 'patients',
            'middleware' => ['patientProgramSecurity'],
        ], function () {
            Route::get('without-scheduled-calls', [
                'uses' => 'API\Admin\CallsController@patientsWithoutScheduledCalls',
                'as'   => 'patients.without-scheduled-calls',
            ])->middleware('permission:patient.read,careplan.read,call.read');

            Route::get('without-inbound-calls', [
                'uses' => 'API\Admin\CallsController@patientsWithoutInboundCalls',
                'as'   => 'patients.without-inbound-calls',
            ])->middleware('permission:patient.read,call.read');

            Route::group([
                'prefix' => '{userId}',
            ], function () {
                Route::get('', 'PatientController@getPatient')->middleware('permission:patient.read');

                Route::group([
                    'prefix' => 'biometrics',
                ], function () {
                    Route::get('', 'PatientController@getBiometrics')->middleware('permission:biometric.read');
                    Route::post('', 'PatientController@addBiometric')->middleware('permission:biometric.create');
                    Route::delete(
                        '{id}',
                        'PatientController@removeBiometric'
                    )->middleware('permission:biometric.delete');
                });

                Route::group([
                    'prefix' => 'problems',
                ], function () {
                    Route::get('', 'PatientController@getProblems')->middleware('permission:patientProblem.read');
                    Route::post(
                        '',
                        'PatientController@addCpmProblem'
                    )->middleware('permission:patientProblem.create,patientProblem.update');
                    Route::get('cpm', 'PatientController@getCpmProblems')->middleware('permission:patientProblem.read');
                    Route::delete(
                        'cpm/{cpmId}',
                        'PatientController@removeCpmProblem'
                    )->middleware('permission:instruction.delete,patientProblem.delete');
                    Route::get('ccd', 'PatientController@getCcdProblems')->middleware('permission:patientProblem.read');
                    Route::post(
                        'ccd',
                        'PatientController@addCcdProblem'
                    )->middleware('permission:patientProblem.create');
                    Route::put(
                        'ccd/{ccdId}',
                        'PatientController@editCcdProblem'
                    )->middleware('permission:patientProblem.update');
                    Route::delete(
                        'ccd/{ccdId}',
                        'PatientController@removeCcdProblem'
                    )->middleware('permission:patientProblem.delete');
                });

                Route::group([
                    'prefix' => 'allergies',
                ], function () {
                    Route::get('', 'PatientController@getCcdAllergies')->middleware('permission:allergy.read');
                    Route::post('', 'PatientController@addCcdAllergies')->middleware('permission:allergy.create');
                    Route::delete(
                        '{allergyId}',
                        'PatientController@deleteCcdAllergy'
                    )->middleware('permission:allergy.delete');
                });

                Route::group([
                    'prefix' => 'symptoms',
                ], function () {
                    Route::get('', 'PatientController@getSymptoms')->middleware('permission:symptom.read');
                    Route::post('', 'PatientController@addSymptom')->middleware('permission:symptom.create');
                    Route::delete(
                        '{symptomId}',
                        'PatientController@removeSymptom'
                    )->middleware('permission:symptom.delete');
                });

                Route::group([
                    'prefix' => 'medication',
                ], function () {
                    Route::get('', 'PatientController@getMedication')->middleware('permission:medication.read');
                    Route::post('', 'PatientController@addMedication')->middleware('permission:medication.create');
                    Route::put('{id}', 'PatientController@editMedication')->middleware('permission:medication.update');
                    Route::delete(
                        '{medicationId}',
                        'PatientController@removeMedication'
                    )->middleware('permission:medication.delete');
                    Route::get(
                        'groups',
                        'PatientController@getMedicationGroups'
                    )->middleware('permission:medication.read');
                });

                Route::group([
                    'prefix' => 'appointments',
                ], function () {
                    Route::get('', 'PatientController@getAppointments')->middleware('permission:appointment.read');
                    Route::post('', 'PatientController@addAppointment')->middleware('permission:appointment.create');
                    Route::delete(
                        '{id}',
                        'PatientController@removeAppointment'
                    )->middleware('permission:appointment.delete');
                });

                Route::group([
                    'prefix' => 'providers',
                ], function () {
                    Route::get('', 'PatientController@getProviders')->middleware('permission:provider.read');
                    Route::post('', 'PatientController@addProvider')->middleware('permission:provider.create');
                    Route::delete('{id}', 'PatientController@removeProvider')->middleware('permission:provider.delete');
                });
            });

            Route::get(
                '{userId}/lifestyles',
                'PatientController@getLifestyles'
            )->middleware('permission:lifestyle.read');
            Route::post(
                '{userId}/lifestyles',
                'PatientController@addLifestyle'
            )->middleware('permission:lifestyle.create');
            Route::delete(
                '{userId}/lifestyles/{lifestyleId}',
                'PatientController@removeLifestyle'
            )->middleware('permission:lifestyle.delete');
            Route::get('{userId}/misc', 'PatientController@getMisc')->middleware('permission:misc.read');
            Route::get(
                '{userId}/misc/{miscTypeId}',
                'PatientController@getMiscByType'
            )->middleware('permission:misc.read');
            Route::post('{userId}/misc', 'PatientController@addMisc')->middleware('permission:misc.create');
            Route::post(
                '{userId}/misc/{miscId}/instructions',
                'PatientController@addInstructionToMisc'
            )->middleware('permission:misc.create,misc.delete');
            Route::delete(
                '{userId}/misc/{miscId}/instructions/{instructionId}',
                'PatientController@removeInstructionFromMisc'
            )->middleware('permission:misc.delete');
            Route::delete(
                '{userId}/misc/{miscId}',
                'PatientController@removeMisc'
            )->middleware('permission:misc.delete');
            Route::get('{userId}/notes', 'PatientController@getNotes')->middleware('permission:note.read');
            Route::post('{userId}/notes', 'PatientController@addNote')->middleware('permission:note.create');
            Route::put('{userId}/notes/{id}', 'PatientController@editNote')->middleware('permission:note.update');
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

            Route::resource('', 'PatientController')->middleware('permission:patient.read');
        });

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
                'API\PracticeController@getLocationProviders'
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

            Route::get('{practiceId}/patients/without-scheduled-calls', [
                'uses' => 'API\Admin\CallsController@patientsWithoutScheduledCalls',
                'as'   => 'practice.patients.without-scheduled-calls',
            ])->middleware('permission:patient.read,careplan.read');

            Route::get('{practiceId}/patients/without-inbound-calls', [
                'uses' => 'API\Admin\CallsController@patientsWithoutInboundCalls',
                'as'   => 'practice.patients.without-inbound-calls',
            ])->middleware('permission:patient.read');
        });

        Route::resource('profile', 'API\ProfileController')->middleware('permission:user.read,role.read');

        Route::resource('nurses', 'API\NurseController')->middleware('permission:nurse.read');

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
                'uses' => 'MedicalRecordImportController@import',
                'as'   => 'imported.records.confirm',
            ]);

            Route::get('records/delete', 'MedicalRecordImportController@deleteRecords');
        });
    });

    Route::resource('profiles', 'API\ProfileController')->middleware('permission:user.read,role.read');

    Route::resource('user.care-plan', 'API\PatientCarePlanController')->middleware('permission:careplan.read');

//    Route::resource('user.care-team', 'API\CareTeamController')->middleware('permission:carePerson.create,carePerson.read,carePerson.update,carePerson.delete');
    Route::get('user/{user}/care-team', [
        'uses' => 'API\CareTeamController@index',
        'as'   => 'user.care-team.index',
    ])->middleware('permission:carePerson.read');
    Route::get('user/{user}/care-team', [
        'uses' => 'API\CareTeamController@index',
        'as'   => 'user.care-team.index',
    ])->middleware('permission:carePerson.read');
    Route::delete('user/{user}/care-team/{care_team}', [
        'uses' => 'API\CareTeamController@destroy',
        'as'   => 'user.care-team.destroy',
    ])->middleware('permission:carePerson.delete');
    Route::patch('user/{user}/care-team/{care_team}', [
        'uses' => 'API\CareTeamController@update',
        'as'   => 'user.care-team.update',
    ])->middleware('permission:carePerson.update');
    Route::get('user/{user}/care-team/{care_team}/edit', [
        'uses' => 'API\CareTeamController@edit',
        'as'   => 'user.care-team.edit',
    ])->middleware('permission:carePerson.read');

    Route::resource(
        'practice.locations',
        'API\PracticeLocationsController'
    )->middleware('permission:location.create,location.read,location.update,location.delete');
    Route::get('practice/{practice}/locations', [
        'uses' => 'API\PracticeLocationsController@index',
        'as'   => 'practice.locations.index',
    ])->middleware('permission:location.read');

    Route::resource(
        'practice.users',
        'API\PracticeStaffController'
    )->middleware('permission:practiceStaff.create,practiceStaff.read,practiceStaff.update,practiceStaff.delete');

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

    Route::post(
        'care-docs/{patient_id}',
        'API\PatientCareDocumentsController@uploadCareDocuments'
    );

    Route::get('care-docs/{patient_id}/{show_past?}', [
        'uses' => 'API\PatientCareDocumentsController@getCareDocuments',
        'as'   => 'get.care-docs',
    ]);

    Route::get('download-care-document/{patient_id}/{doc_id}', [
        'uses' => 'API\PatientCareDocumentsController@downloadCareDocument',
        'as'   => 'download.care-doc',
    ]);

    Route::patch(
        'work-hours/{id}',
        'CareCenter\WorkScheduleController@updateDailyHours'
    )->middleware('permission:workHours.update');
    // end API

    Route::resource(
        'settings/email',
        'EmailSettingsController'
    )->middleware('permission:emailSettings.update,emailSettings.create');

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

    Route::get('ccd/show/{ccdaId}', [
        'uses' => 'CCDViewer\CCDViewerController@show',
        'as'   => 'get.CCDViewerController.show',
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

    Route::get('imported-medical-records/{imrId}/training-results', [
        'uses' => 'ImporterController@getTrainingResults',
        'as'   => 'get.importer.training.results',
    ])->middleware('permission:ccda.read');

    Route::post('importer/train', [
        'uses' => 'ImporterController@train',
        'as'   => 'post.train.importing.algorithm',
    ])->middleware('permission:ccda.create');

    Route::post('importer/train/store', [
        'uses' => 'ImporterController@storeTrainingFeatures',
        'as'   => 'post.store.training.features',
    ])->middleware('permission:ccda.update');

    // CCD Importer Routes
    Route::group([
        'middleware' => [
            'permission:ccd-import',
        ],
        'prefix' => 'ccd-importer',
    ], function () {
        Route::get('create', [
            'uses' => 'ImporterController@create',
            'as'   => 'import.ccd',
        ]);

        Route::get('', [
            'uses' => 'ImporterController@remix',
            'as'   => 'import.ccd.remix',
        ]);

        Route::post('imported-medical-records', [
            'uses' => 'ImporterController@uploadRawFiles',
            'as'   => 'upload.ccda',
        ]);

        Route::get('uploaded-ccd-items/{importedMedicalRecordId}/edit', 'ImportedMedicalRecordController@edit');

        Route::post('demographics', 'EditImportedCcda\DemographicsImportsController@store');

        Route::post('import', 'MedicalRecordImportController@importDEPRECATED');
    });

    //CCD Parser Demo Route
    Route::get('ccd-parser-demo', 'CCDParserDemoController@index');

    //
    // PROVIDER UI (/manage-patients, /reports, ect)
    //

    // **** PATIENTS (/manage-patients/
    Route::group([
        'prefix'     => 'manage-patients/',
        'middleware' => ['patientProgramSecurity'],
    ], function () {
        Route::group(['prefix' => 'offline-activity-time-requests'], function () {
            Route::get('', [
                'uses' => 'OfflineActivityTimeRequestController@index',
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

        Route::group(['prefix' => 'settings'], function () {
            Route::get('', [
                'uses' => 'UserSettingsController@show',
                'as'   => 'user.settings.manage',
            ]);
        });

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
        'middleware' => ['patientProgramSecurity', 'checkWebSocketServer'],
    ], function () {
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
        'middleware' => ['patientProgramSecurity', 'checkWebSocketServer'],
    ], function () {
        Route::get('call', [
            'uses' => 'Patient\PatientController@showCallPatientPage',
            'as'   => 'patient.show.call.page',
        ])->middleware('permission:patient.read');
        Route::get('summary', [
            'uses' => 'Patient\PatientController@showPatientSummary',
            'as'   => 'patient.summary',
        ])->middleware('permission:patient.read,patientProblem.read,misc.read,observation.read,patientSummary.read');
        Route::get('summary-biochart', [
            'uses' => 'ReportsController@biometricsCharts',
            'as'   => 'patient.charts',
        ])->middleware('permission:patient.read,biometric.read');
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
        ])->middleware('permission:careplan.read');

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

        Route::get('approve-careplan/{viewNext?}', [
            'uses' => 'ProviderController@approveCarePlan',
            'as'   => 'patient.careplan.approve',
        ])->middleware('permission:care-plan-approve,care-plan-qa-approve');

        Route::post('not-eligible/{viewNext?}', [
            'uses' => 'ProviderController@removePatient',
            'as'   => 'patient.careplan.not.eligible',
        ])->middleware('permission:care-plan-approve');

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
            ])->middleware('permission:patient.read');
            Route::post('store', [
                'uses' => 'NotesController@store',
                'as'   => 'patient.note.store',
            ])->middleware('permission:note.create,patient.update,patientSummary.update');
            Route::get('{showAll?}', [
                'uses' => 'NotesController@index',
                'as'   => 'patient.note.index',
            ])->middleware('permission:patient.read,provider.read,note.read,appointment.read,activity.read');
            Route::get('view/{noteId}', [
                'uses' => 'NotesController@show',
                'as'   => 'patient.note.view',
            ])->middleware('permission:patient.read,provider.read,note.read');
            Route::post('send/{noteId}', [
                'uses' => 'NotesController@send',
                'as'   => 'patient.note.send',
            ])->middleware('permission:note.send');
            Route::post('{noteId}/addendums', [
                'uses' => 'NotesController@storeAddendum',
                'as'   => 'note.store.addendum',
            ])->middleware('permission:addendum.create');
        });

        Route::post('ccm/toggle', [
            'uses' => 'CCMComplexToggleController@toggle',
            'as'   => 'patient.ccm.toggle',
        ])->middleware('permission:patientSummary.update');

        Route::get('progress', [
            'uses' => 'ReportsController@index',
            'as'   => 'patient.reports.progress',
        ])->middleware('permission:patient.read,provider.read,biometric.read,biometric.update,medication.read,medication.update');

        Route::group(['prefix' => 'offline-activity-time-requests'], function () {
            Route::get('create', [
                'uses' => 'OfflineActivityTimeRequestController@create',
                'as'   => 'offline-activity-time-requests.create',
            ])->middleware('permission:patient.read,offlineActivityRequest.create');
            Route::post('store', [
                'uses' => 'OfflineActivityTimeRequestController@store',
                'as'   => 'offline-activity-time-requests.store',
            ])->middleware('permission:offlineActivityRequest.create');
        });

        Route::group(['prefix' => 'activities'], function () {
            Route::get('create', [
                'uses' => 'ActivityController@create',
                'as'   => 'patient.activity.create',
            ])->middleware('permission:patient.read,offlineActivity.create');
            Route::post('store', [
                'uses' => 'ActivityController@store',
                'as'   => 'patient.activity.store',
            ])->middleware('permission:activity.create,offlineActivity.create,patientSummary.create,patientSummary.update');
            Route::get('view/{actId}', [
                'uses' => 'ActivityController@show',
                'as'   => 'patient.activity.view',
            ])->middleware('permission:activity.read,patient.read,provider.read');
            Route::get('', [
                'uses' => 'ActivityController@providerUIIndex',
                'as'   => 'patient.activity.providerUIIndex',
            ])->middleware('permission:activity.read,patient.read,provider.read');

            Route::get('getCurrent', [
                'uses' => 'ActivityController@getCurrentForPatient',
                'as'   => 'patient.activity.get.current.for.patient',
            ])->middleware('permission:activity.read,patient.read,provider.read');
        });

        //call scheduling
        Route::group(['prefix' => 'calls'], function () {
            Route::get('', [
                'uses' => 'CallController@index',
                'as'   => 'call.index',
            ])->middleware('permission:call.read');
            Route::get('create', [
                'uses' => 'CallController@create',
                'as'   => 'call.create',
            ])->middleware('permission:call.create');
            Route::post('schedule', [
                'uses' => 'CallController@schedule',
                'as'   => 'call.schedule',
            ])->middleware('permission:call.create');
            Route::get('edit/{actId}', [
                'uses' => 'CallController@edit',
                'as'   => 'call.edit',
            ]);
            Route::get('next', [
                'uses' => 'CallController@getPatientNextScheduledCallJson',
                'as'   => 'call.next',
            ])->middleware('permission:call.read');
            Route::post('reschedule', [
                'uses' => 'CallController@reschedule',
                'as'   => 'call.reschedule',
            ])->middleware('permission:call.update');
        });
    });

    //
    // ADMIN (/admin)
    //
    // NOTE: in two route groups. One for software-only and one for super admins
    //

    Route::group([
        'middleware' => [
            'auth',
            'permission:admin-access,practice-admin',
        ],
        'prefix' => 'admin',
    ], function () {
        Route::get('calls-v2', [
            'uses' => 'Admin\PatientCallManagementController@remixV2',
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

        Route::group(['prefix' => 'eligibility-batches'], function () {
            Route::get('pending-jobs/count', [
                'uses' => 'EligibilityBatchController@allJobsCount',
                'as'   => 'all.eligibility.jobs.count',
            ])->middleware('permission:batch.read');

            Route::get('', [
                'uses' => 'EligibilityBatchController@index',
                'as'   => 'eligibility.batches.index',
            ])->middleware('permission:batch.read');

            Route::get('google-drive/create', [
                'uses' => 'EligibilityBatchController@googleDriveCreate',
                'as'   => 'eligibility.batches.google.drive.create',
            ]);

            Route::get('csv/create', [
                'uses' => 'EligibilityBatchController@csvCreate',
                'as'   => 'eligibility.batches.csv.create',
            ]);

            Route::group(['prefix' => '{batch}'], function () {
                Route::get('', [
                    'uses' => 'EligibilityBatchController@show',
                    'as'   => 'eligibility.batch.show',
                ])->middleware('permission:batch.read,practice.read,ccda.read');

                Route::get('/counts', [
                    'uses' => 'EligibilityBatchController@getCounts',
                    'as'   => 'eligibility.batch.getCounts',
                ])->middleware('permission:enrollee.read,ccda.read');

                Route::get('/eligible-csv', [
                    'uses' => 'EligibilityBatchController@downloadEligibleCsv',
                    'as'   => 'eligibility.download.eligible',
                ])->middleware('permission:enrollee.read');

                Route::get('/reprocess', [
                    'uses' => 'EligibilityBatchController@getReprocess',
                    'as'   => 'get.eligibility.reprocess',
                ])->middleware('permission:enrollee.read');

                Route::post('/reprocess', [
                    'uses' => 'EligibilityBatchController@postReprocess',
                    'as'   => 'post.eligibility.reprocess',
                ])->middleware('permission:enrollee.read');

                Route::get('/last-import-session-logs', [
                    'uses' => 'EligibilityBatchController@getLastImportLog',
                    'as'   => 'eligibility.download.last.import.logs',
                ])->middleware('permission:batch.read');

                Route::get('/download-patient-list-csv', [
                    'uses' => 'EligibilityBatchController@downloadCsvPatientList',
                    'as'   => 'eligibility.download.csv.patient.list',
                ])->middleware('permission:batch.read');

                Route::get('/batch-logs-csv', [
                    'uses' => 'EligibilityBatchController@downloadBatchLogCsv',
                    'as'   => 'eligibility.download.logs.csv',
                ])->middleware('permission:batch.read');
            });
        });

        Route::group(['prefix' => 'ca-director'], function () {
            Route::get('', [
                'uses' => 'EnrollmentDirectorController@index',
                'as'   => 'ca-director.index',
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

            Route::post('/mark-ineligible', [
                'uses' => 'EnrollmentDirectorController@markEnrolleesAsIneligible',
                'as'   => 'ca-director.mark-ineligible',
            ]);

            Route::post('/edit-enrollee', [
                'uses' => 'EnrollmentDirectorController@editEnrolleeData',
                'as'   => 'ca-director.edit-enrollee',
            ]);

            Route::post('/add-enrollee-custom-filter', [
                'uses' => 'EnrollmentDirectorController@addEnrolleeCustomFilter',
                'as'   => 'ca-director.add-enrollee-custom-filter',
            ]);
        });

        Route::group(['prefix' => 'enrollees'], function () {
            Route::get('', [
                'uses' => 'EnrolleesController@index',
                'as'   => 'admin.enrollees.index',
            ])->middleware('permission:enrollee.read,practice.read');
            Route::get('batch/{batch}', [
                'uses' => 'EnrolleesController@showBatch',
                'as'   => 'admin.enrollees.show.batch',
            ])->middleware('permission:enrollee.read,practice.read,batch.read');
            Route::post('{batch}/import', [
                'uses' => 'EnrolleesController@import',
                'as'   => 'admin.enrollees.import',
            ])->middleware('permission:enrollee.read,enrollee.update');
            Route::post('import', [
                'uses' => 'EnrolleesController@import',
                'as'   => 'admin.enrollees.import.from.all.practices',
            ])->middleware('permission:enrollee.read,enrollee.update');
            Route::post('/import-array-of-ids', [
                'uses' => 'EnrolleesController@importArray',
                'as'   => 'admin.enrollees.import.array',
            ])->middleware('permission:enrollee.read,enrollee.update');
            Route::post('/import-using-medical-record-id', [
                'uses' => 'EnrolleesController@importMedicalRecords',
                'as'   => 'admin.enrollees.import.medical.records',
            ])->middleware('permission:ccd-import');
        });

        Route::resource('saas-accounts', 'Admin\CRUD\SaasAccountController')->middleware('permission:saas.create');

        Route::get(
            'eligible-lists/phoenix-heart',
            'Admin\WelcomeCallListController@makePhoenixHeartCallList'
        )->middleware('permission:batch.create');

        Route::view('api-clients', 'admin.manage-api-clients');

//        Route::resource('medication-groups-maps', 'MedicationGroupsMapController')->middleware('permission:medicationGroup.read,medicationGroup.create,medicationGroup.delete');

        Route::get('medication-groups-maps', [
            'uses' => 'MedicationGroupsMapController@index',
            'as'   => 'medication-groups-maps.index',
        ])->middleware('permission:medicationGroup.read');

        Route::post('medication-groups-maps', [
            'uses' => 'MedicationGroupsMapController@store',
            'as'   => 'medication-groups-maps.store',
        ])->middleware('permission:medicationGroup.create');

        Route::delete('medication-groups-maps', [
            'uses' => 'MedicationGroupsMapController@destroy',
            'as'   => 'medication-groups-maps.destroy',
        ])->middleware('permission:medicationGroup.delete');

        Route::post('get-athena-ccdas', [
            'uses' => 'CcdApi\Athena\AthenaApiController@getCcdas',
            'as'   => 'get.athena.ccdas',
        ])->middleware('permission:ccd-import');

        Route::post('athena-pull', [
            'uses' => 'Admin\DashboardController@pullAthenaEnrollees',
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

        // LOGGER
        Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

        Route::get('nurses/windows', [
            'uses' => 'CareCenter\WorkScheduleController@getAllNurseSchedules',
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

        Route::get('invites/create', [
            'uses' => 'InviteController@create',
            'as'   => 'invite.create',
        ]);

        Route::post('invites/store', [
            'uses' => 'InviteController@store',
            'as'   => 'invite.store',
        ])->middleware('permission:invite.create');

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

        Route::post('make-welcome-call-list', [
            'uses' => 'Admin\WelcomeCallListController@makeWelcomeCallList',
            'as'   => 'make.welcome.call.list',
        ])->middleware('permission:batch.create');

        Route::get('families/create', [
            'uses' => 'FamilyController@create',
            'as'   => 'family.create',
        ])->middleware('permission:patient.read');

        Route::post('general-comments/import', [
            'uses' => 'Admin\UploadsController@postGeneralCommentsCsv',
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

                Route::post('/open', [
                    'uses' => 'Billing\PracticeInvoiceController@openMonthlySummaryStatus',
                    'as'   => 'monthly.billing.open.month',
                ])->middleware('permission:patientSummary.update');

                Route::post('/close', [
                    'uses' => 'Billing\PracticeInvoiceController@closeMonthlySummaryStatus',
                    'as'   => 'monthly.billing.close.month',
                ])->middleware('permission:patientSummary.update');

                Route::post('/status/update', [
                    'uses' => 'Billing\PracticeInvoiceController@updateStatus',
                    'as'   => 'monthly.billing.status.update',
                ])->middleware('permission:patientSummary.update');

                Route::post('/counts', [
                    'uses' => 'Billing\PracticeInvoiceController@counts',
                    'as'   => 'monthly.billing.count',
                ])->middleware('permission:patientSummary.read');

                Route::post('/storeProblem', [
                    'uses' => 'Billing\PracticeInvoiceController@storeProblem',
                    'as'   => 'monthly.billing.store-problem',
                ])->middleware('permission:patientSummary.update');

                Route::post('/getBillingCount', [
                    'uses' => 'Billing\PracticeInvoiceController@getCounts',
                    'as'   => 'monthly.billing.counts',
                ])->middleware('permission:patientSummary.update');

                Route::post('/send', [
                    'uses' => 'Billing\PracticeInvoiceController@send',
                    'as'   => 'monthly.billing.send',
                ])->middleware('permission:patientSummary.read');
            });

            Route::get('patients-for-insurance-check', [
                'uses' => 'Reports\PatientsForInsuranceCheck@make',
                'as'   => 'get.patients.for.insurance.check',
            ])->middleware('permission:patient.read,practice.read,insurance.read');

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

            Route::get('ethnicity', [
                'uses' => 'Admin\Reports\EthnicityReportController@getReport',
                'as'   => 'EthnicityReportController.getReport',
            ])->middleware('permission:ethnicityReport.create');

            Route::get('call', [
                'uses' => 'Admin\Reports\CallReportController@exportxls',
                'as'   => 'CallReportController.exportxls',
            ])->middleware('permission:call.read,note.read,patient.read,patientSummary.read');

            Route::get('call-v2', [
                'uses' => 'Admin\Reports\CallReportController@exportxlsV2',
                'as'   => 'CallReportController.exportxlsv2',
            ])->middleware('permission:call.read,note.read,patient.read,patientSummary.read');

            Route::get('provider-usage', [
                'uses' => 'Admin\Reports\ProviderUsageReportController@index',
                'as'   => 'ProviderUsageReportController.index',
            ])->middleware('permission:provider.read,nurse.read');

            Route::get('provider-monthly-usage', [
                'uses' => 'Admin\Reports\ProviderMonthlyUsageReportController@index',
                'as'   => 'ProviderMonthlyUsageReportController.index',
            ])->middleware('permission:provider.read,nurse.read');

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

            Route::group([
                'prefix' => 'ops-dashboard',
            ], function () {
                Route::get('/index', [
                    'uses' => 'OpsDashboardController@index',
                    'as'   => 'OpsDashboard.index',
                ])->middleware('permission:opsReport.read');
                Route::get('/index/csv', [
                    'uses' => 'OpsDashboardController@dailyCsv',
                    'as'   => 'OpsDashboard.dailyCsv',
                ])->middleware('permission:opsReport.read');
                Route::get('/ops-csv/{fileName}/{collection}', [
                    'uses' => 'OpsDashboardController@downloadCsvReport',
                    'as'   => 'OpsDashboard.makeCsv',
                ])->middleware('permission:opsReport.read');

                Route::get('/lost-added', [
                    'uses' => 'OpsDashboardController@getLostAdded',
                    'as'   => 'OpsDashboard.lostAdded',
                ])->middleware('permission:opsReport.read');
                Route::get('/patient-list-index', [
                    'uses' => 'OpsDashboardController@getPatientListIndex',
                    'as'   => 'OpsDashboard.patientListIndex',
                ])->middleware('permission:opsReport.read');

                Route::get('/patient-list', [
                    'uses' => 'OpsDashboardController@getPatientList',
                    'as'   => 'OpsDashboard.patientList',
                ])->middleware('permission:opsReport.read');
                Route::post('/make-excel', [
                    'uses' => 'OpsDashboardController@makeExcelPatientReport',
                    'as'   => 'OpsDashboard.makeExcel',
                ])->middleware('permission:opsReport.read');

                //billing churn
                Route::get('/billing-churn', [
                    'uses' => 'OpsDashboardController@getBillingChurn',
                    'as'   => 'OpsDashboard.billingChurn',
                ])->middleware('permission:opsReport.read');

                //old dashboard
                Route::get('/total-data', [
                    'uses' => 'OpsDashboardController@getTotalPatientData',
                    'as'   => 'OpsDashboard.totalData',
                ]);
                Route::get('/paused-patient-list', [
                    'uses' => 'OpsDashboardController@getPausedPatientList',
                    'as'   => 'OpsDashboard.pausedPatientList',
                ]);
//                Route::get('/patient-list/{type}/{date}/{dateType}/{practiceId?}', [
//                    'uses' => 'OpsDashboardController@getList',
//                    'as'   => 'OpsDashboard.patientList'
//                ]);
                Route::get('/patients-by-practice', [
                    'uses' => 'OpsDashboardController@getPatientsByPractice',
                    'as'   => 'OpsDashboard.patientsByPractice',
                ]);
            });
        });

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
        ])->middleware('permission:excelReport.create');
        Route::get('excelReportT2', [
            'uses' => 'ReportsController@excelReportT2',
            'as'   => 'excel.report.t2',
        ])->middleware('permission:excelReport.create');
        Route::get('excelReportT3', [
            'uses' => 'ReportsController@excelReportT3',
            'as'   => 'excel.report.t3',
        ])->middleware('permission:excelReport.create');
        Route::get('excelReportT4', [
            'uses' => 'ReportsController@excelReportT4',
            'as'   => 'excel.report.t4',
        ])->middleware('permission:excelReport.create');

        // dashboard
        Route::get('', [
            'uses' => 'Admin\DashboardController@index',
            'as'   => 'admin.dashboard',
        ]);
        Route::get('testplan', [
            'uses' => 'Admin\DashboardController@testplan',
            'as'   => 'admin.testplan',
        ]);

        Route::get('impersonate/take/{id}', [
            'uses' => '\Lab404\Impersonate\Controllers\ImpersonateController@take',
            'as'   => 'impersonate',
        ]);

        // appConfig
        Route::group([
            'middleware' => [
                'permission:appConfig.read',
            ],
        ], function () {
            Route::resource('appConfig', 'Admin\AppConfigController');
        });

        Route::group([
        ], function () {
            Route::post('appConfig/{id}/edit', [
                'uses' => 'Admin\AppConfigController@update',
                'as'   => 'admin.appConfig.update',
            ])->middleware('permission:appConfig.update');
            Route::get('appConfig/{id}/destroy', [
                'uses' => 'Admin\AppConfigController@destroy',
                'as'   => 'admin.appConfig.destroy',
            ])->middleware('permission:appConfig.delete');
        });

        // activities
        Route::group([
            'middleware' => [
                'permission:activity.read',
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
        ], function () {
            Route::get('users', [
                'uses' => 'UserController@index',
                'as'   => 'admin.users.index',
            ])->middleware('permission:user.read,practice.read');
            Route::post('users', [
                'uses' => 'UserController@store',
                'as'   => 'admin.users.store',
            ])->middleware('permission:user.create');
            Route::get('users/create', [
                'uses' => 'UserController@create',
                'as'   => 'admin.users.create',
            ])->middleware('permission:user.read,practice.read,location.read,role.read');
            Route::get('users/doAction', [
                'uses' => 'UserController@doAction',
                'as'   => 'admin.users.doAction',
            ]);
            Route::get('users/{id}/edit', [
                'uses' => 'UserController@edit',
                'as'   => 'admin.users.edit',
            ])->middleware('permission:user.read,practice.read,location.read,role.read');
            Route::get('users/{id}/destroy', [
                'uses' => 'UserController@destroy',
                'as'   => 'admin.users.destroy',
            ])->middleware('permission:user.delete');
            Route::post('users/{id}/edit', [
                'uses' => 'UserController@update',
                'as'   => 'admin.users.update',
            ])->middleware('permission:user.update');
            Route::get('users/createQuickPatient/{primaryProgramId}', [
                'uses' => 'UserController@createQuickPatient',
                'as'   => 'admin.users.createQuickPatient',
            ])->middleware('permission:patient.read');
            Route::post('users/createQuickPatient/', [
                'uses' => 'UserController@storeQuickPatient',
                'as'   => 'admin.users.storeQuickPatient',
            ])->middleware('permission:patient.create');
            Route::get('users/{id}/msgcenter', [
                'uses' => 'UserController@showMsgCenter',
                'as'   => 'admin.users.msgCenter',
            ]);
            Route::post('users/{id}/msgcenter', [
                'uses' => 'UserController@showMsgCenter',
                'as'   => 'admin.users.msgCenterUpdate',
            ]);

            Route::get('calls', [
                'uses' => 'Admin\PatientCallManagementController@remix',
                'as'   => 'admin.patientCallManagement.index',
            ]);

            Route::get('time-tracker', [
                'uses' => 'Admin\TimeTrackerController@index',
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

        // roles
        Route::group([
            'middleware' => [
                'permission:role.read',
            ],
        ], function () {
            Route::resource(
                'roles',
                'Admin\RoleController'
            )->middleware('permission:user.read,practice.read,location.read');
        });

        Route::group([
        ], function () {
            Route::post('roles/{id}/edit', [
                'uses' => 'Admin\RoleController@update',
                'as'   => 'admin.roles.update',
            ])->middleware('permission:role.update');
        });

        // permissions
        Route::group([
        ], function () {
            Route::resource(
                'permissions',
                'Admin\PermissionController'
            )->middleware('permission:permission.read,permission.create,permission.update,permission.delete');
        });
        Route::get('roles-permissions', [
            'uses' => 'Admin\PermissionController@makeRoleExcel',
            'as'   => 'admin.permissions.makeRoleExcel',
        ]);
        Route::get('routes-permissions', [
            'uses' => 'Admin\PermissionController@makeRouteExcel',
            'as'   => 'admin.permissions.makeRouteExcel',
        ]);
        Route::group([
            'middleware' => [
                'permission:permission.update',
            ],
        ], function () {
            Route::post('permissions/{id}/edit', [
                'uses' => 'Admin\PermissionController@update',
                'as'   => 'admin.permissions.update',
            ]);
        });

        //these fall under the admin-access permission
        Route::get('reports/nurse/invoice', [
            'uses' => 'NurseController@makeInvoice',
            'as'   => 'admin.reports.nurse.invoice',
        ])->middleware('permission:nurseInvoice.read');

        Route::post('reports/nurse/invoice/generate', [
            'uses' => 'NurseController@generateInvoice',
            'as'   => 'admin.reports.nurse.generate',
        ])->middleware('permission:nurseInvoice.create');

        Route::post('reports/nurse/invoice/send', [
            'uses' => 'NurseController@sendInvoice',
            'as'   => 'admin.reports.nurse.send',
        ])->middleware('permission:nurseInvoice.view');

        Route::get('reports/nurse/daily', [
            'uses' => 'NurseController@makeDailyReport',
            'as'   => 'admin.reports.nurse.daily',
        ]);

        Route::get('reports/nurse/daily/data', [
            'uses' => 'NurseController@dailyReport',
            'as'   => 'admin.reports.nurse.daily.data',
        ])->middleware('permission:nurseReport.create');

        Route::get('reports/nurse/allocation', [
            'uses' => 'NurseController@monthlyOverview',
            'as'   => 'admin.reports.nurse.allocation',
        ])->middleware('permission:nurseReport.read');

        Route::get('reports/nurse/monthly', [
            'uses' => 'NurseController@monthlyReport',
            'as'   => 'admin.reports.nurse.monthly',
        ])->middleware('permission:nurseReport.create');

        Route::get('reports/nurse/weekly', [
            'uses' => 'NursesWeeklyRepController@index',
            'as'   => 'admin.reports.nurse.weekly',
        ])->middleware('permission:nurseReport.read');
        //STATS
        Route::get('reports/nurse/stats', [
            'uses' => 'NurseController@makeHourlyStatistics',
            'as'   => 'stats.nurse.info',
        ]);

        // questions TODO: review permissions for this group
        Route::group([
            'middleware' => [
                'permission:practice.read',
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
        ], function () {
            Route::resource(
                'comments',
                'Admin\CommentController'
            )->middleware('permission:comment.create,comment.read,comment.update,comment.delete');
            Route::resource(
                'observations',
                'Admin\ObservationController'
            )->middleware('permission:observation.create,observation.read,observation.update,observation.delete');
        });

        Route::group([
        ], function () {
            Route::get('observations/{id}/destroy', [
                'uses' => 'Admin\ObservationController@destroy',
                'as'   => 'admin.observations.destroy',
            ])->middleware('permission:observation.delete');
            Route::post('observations/{id}/edit', [
                'uses' => 'Admin\ObservationController@update',
                'as'   => 'admin.observations.update',
            ])->middleware('permission:observation.update');

            Route::post('comments/{id}/edit', [
                'uses' => 'Admin\CommentController@update',
                'as'   => 'admin.comments.update',
            ])->middleware('permission:comment.update');
            Route::get('comments/{id}/destroy', [
                'uses' => 'Admin\CommentController@destroy',
                'as'   => 'admin.comments.destroy',
            ])->middleware('permission:comment.delete');
        });

        Route::group([
            'middleware' => [
                'permission:observation.read',
            ],
        ], function () {
            Route::post('observations', [
                'uses' => 'Admin\ObservationController@index',
                'as'   => 'admin.observations.index',
            ]);
        });

        //observations dashboard

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

    // CARE-CENTER GROUP
    Route::group([
        'middleware' => ['role:care-center,administrator'],
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
    Route::post('callupdate', [
        'uses' => 'CallController@update',
        'as'   => 'api.callupdate',
    ]);
    Route::post('callcreate-multi', [
        'uses' => 'CallController@createMulti',
        'as'   => 'api.callcreate-multi',
    ]);
});

// Provider Dashboard
Route::group([
    'prefix'     => 'practices/{practiceSlug}',
    'middleware' => [
        'auth',
        'providerDashboardACL:administrator,saas-admin,saas-admin-view-only',
    ],
], function () {
    Route::post('chargeable-services', [
        'uses' => 'Provider\DashboardController@postStoreChargeableServices',
        'as'   => 'provider.dashboard.store.chargeable-services',
    ])->middleware('permission:practiceSetting.create');

    Route::get('chargeable-services', [
        'uses' => 'Provider\DashboardController@getCreateChargeableServices',
        'as'   => 'provider.dashboard.manage.chargeable-services',
    ])->middleware('permission:practiceSetting.read');

    Route::post('invite', [
        'uses' => 'Provider\DashboardController@postStoreInvite',
        'as'   => 'post.store.invite',
    ])->middleware('permission:invite.create');

    Route::post('locations', [
        'uses' => 'Provider\DashboardController@postStoreLocations',
        'as'   => 'provider.dashboard.store.locations',
    ])->middleware('permission:practiceSetting.create');

    Route::post('staff', [
        'uses' => 'Provider\DashboardController@postStoreStaff',
        'as'   => 'provider.dashboard.store.staff',
    ])->middleware('permission:practiceSetting.update');

    Route::post('notifications', [
        'uses' => 'Provider\DashboardController@postStoreNotifications',
        'as'   => 'provider.dashboard.store.notifications',
    ])->middleware('permission:practiceSetting.update');

    Route::get('notifications', [
        'uses' => 'Provider\DashboardController@getCreateNotifications',
        'as'   => 'provider.dashboard.manage.notifications',
    ])->middleware('permission:practiceSetting.read');

    Route::post('practice', [
        'uses' => 'Provider\DashboardController@postStorePractice',
        'as'   => 'provider.dashboard.store.practice',
    ])->middleware('permission:practiceSetting.update');

    Route::get('practice', [
        'uses' => 'Provider\DashboardController@getCreatePractice',
        'as'   => 'provider.dashboard.manage.practice',
    ])->middleware('permission:practiceSetting.read');

    Route::get('staff', [
        'uses' => 'Provider\DashboardController@getCreateStaff',
        'as'   => 'provider.dashboard.manage.staff',
    ])->middleware('permission:practiceSetting.read');

    Route::get('', [
        //        'uses' => 'Provider\DashboardController@getIndex',
        'uses' => 'Provider\DashboardController@getCreateNotifications',
        'as'   => 'provider.dashboard.index',
    ])->middleware('permission:practiceSetting.read');

    Route::get('locations', [
        'uses' => 'Provider\DashboardController@getCreateLocation',
        'as'   => 'provider.dashboard.manage.locations',
    ])->middleware('permission:practiceSetting.read');

    Route::get('enrollment', [
        'uses' => 'Provider\DashboardController@getCreateEnrollment',
        'as'   => 'provider.dashboard.manage.enrollment',
    ])->middleware('permission:practiceSetting.read');

    Route::post('enrollment', [
        'uses' => 'Provider\DashboardController@postStoreEnrollment',
        'as'   => 'provider.dashboard.store.enrollment',
    ])->middleware('permission:practiceSetting.update');
});

// Enrollment Center UI

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
        ])->middleware('permission:enrollee.read,enrollee.update');

        Route::post('/consented', [
            'uses' => 'Enrollment\EnrollmentCenterController@consented',
            'as'   => 'enrollment-center.consented',
        ])->middleware('permission:enrollee.update');

        Route::post('/utc', [
            'uses' => 'Enrollment\EnrollmentCenterController@unableToContact',
            'as'   => 'enrollment-center.utc',
        ])->middleware('permission:enrollee.update');

        Route::post('/rejected', [
            'uses' => 'Enrollment\EnrollmentCenterController@rejected',
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
    'prefix' => 'onboarding',
], function () {
    Route::get('create-invited-user/{code?}', [
        'middleware' => 'verify.invite',
        'uses'       => 'Provider\OnboardingController@getCreateInvitedUser',
        'as'         => 'get.onboarding.create.invited.user',
    ])->middleware('permission:provider.read');

    Route::get('create-practice-lead-user/{code?}', [
        'middleware' => 'verify.invite',
        'uses'       => 'Provider\OnboardingController@getCreatePracticeLeadUser',
        'as'         => 'get.onboarding.create.program.lead.user',
    ])->middleware('permission:provider.read,invite.read,invite.create');

    Route::post('store-invited-user', [
        'uses' => 'Provider\OnboardingController@postStoreInvitedUser',
        'as'   => 'get.onboarding.store.invited.user',
    ])->middleware('permission:provider.update,provider.create,invite.delete');

    Route::post('store-practice-lead-user', [
        'uses' => 'Provider\OnboardingController@postStorePracticeLeadUser',
        'as'   => 'post.onboarding.store.program.lead.user',
    ])->middleware('permission:provider.create');

    Route::group([
        'middleware' => 'auth',
    ], function () {
        Route::post('store-locations/{lead_id}', [
            'uses' => 'Provider\OnboardingController@postStoreLocations',
            'as'   => 'post.onboarding.store.locations',
        ])->middleware('permission:location.update');

        Route::post('store-practice/{lead_id}', [
            'uses' => 'Provider\OnboardingController@postStorePractice',
            'as'   => 'post.onboarding.store.practice',
        ])->middleware('permission:practice.create,provider.update');

        Route::get('{practiceSlug}/locations/create/{lead_id}', [
            'uses' => 'Provider\OnboardingController@getCreateLocations',
            'as'   => 'get.onboarding.create.locations',
        ])->middleware('permission:practice.read');

        Route::get('create-practice/{lead_id}', [
            'uses' => 'Provider\OnboardingController@getCreatePractice',
            'as'   => 'get.onboarding.create.practice',
        ]);

        Route::get('{practiceSlug}/staff/create', [
            'uses' => 'Provider\OnboardingController@getCreateStaff',
            'as'   => 'get.onboarding.create.staff',
        ])->middleware('permission:practice.read');

        Route::post('{practiceSlug}/store-staff', [
            'uses' => 'Provider\OnboardingController@postStoreStaff',
            'as'   => 'post.onboarding.store.staff',
        ])->middleware('permission:provider.create,provider.update,practiceSetting.update');
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
    'prefix'     => 'twilio',
    'middleware' => 'auth',
], function () {
    Route::get('/token', [
        'uses' => 'Twilio\TwilioController@obtainToken',
        'as'   => 'twilio.token',
    ]);
    Route::post('/call/js-create-conference', [
        'uses' => 'Twilio\TwilioController@jsCreateConference',
        'as'   => 'twilio.js.create.conference',
    ]);
    Route::post('/call/get-conference-info', [
        'uses' => 'Twilio\TwilioController@getConferenceInfo',
        'as'   => 'twilio.js.get.conference.info',
    ]);
    Route::post('/call/join-conference', [
        'uses' => 'Twilio\TwilioController@joinConference',
        'as'   => 'twilio.call.join.conference',
    ]);
    Route::post('/call/end', [
        'uses' => 'Twilio\TwilioController@endCall',
        'as'   => 'twilio.call.leave.conference',
    ]);
});

Route::group([
    'prefix' => 'twilio',
], function () {
    Route::post('/call/place', [
        'uses' => 'Twilio\TwilioController@placeCall',
        'as'   => 'twilio.call.place',
    ]);
    Route::post('/call/status', [
        'uses' => 'Twilio\TwilioController@callStatusCallback',
        'as'   => 'twilio.call.status',
    ]);
    Route::post('/call/number-status', [
        'uses' => 'Twilio\TwilioController@dialNumberStatusCallback',
        'as'   => 'twilio.call.number.status',
    ]);
    Route::post('/call/dial-action', [
        'uses' => 'Twilio\TwilioController@dialActionCallback',
        'as'   => 'twilio.call.dial.action',
    ]);
    Route::post('/call/conference-status', [
        'uses' => 'Twilio\TwilioController@conferenceStatusCallback',
        'as'   => 'twilio.call.conference.status',
    ]);
    Route::post('/call/recording-status', [
        'uses' => 'Twilio\TwilioController@recordingStatusCallback',
        'as'   => 'twilio.call.recording.status',
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

    Route::group(['prefix' => 'monthly-billing'], function () {
        Route::get('make', [
            'uses' => 'Billing\PracticeInvoiceController@make',
            'as'   => 'saas-admin.monthly.billing.make',
        ]);

        Route::post('data', [
            'uses' => 'Billing\PracticeInvoiceController@data',
            'as'   => 'saas-admin.monthly.billing.data',
        ]);
    });

    Route::group(['prefix' => 'practice/billing'], function () {
        Route::get('create', [
            'uses' => 'Billing\PracticeInvoiceController@createInvoices',
            'as'   => 'saas-admin.practices.billing.create',
        ]);
    });
});

Route::post('process-eligibility/drive/', [
    'uses' => 'ProcessEligibilityController@fromGoogleDrive',
    'as'   => 'process.eligibility.google.drive',
])->middleware(['auth', 'role:administrator']);

Route::get(
    'process-eligibility/local-zip-from-drive/{dir}/{practiceName}/{filterLastEncounter}/{filterInsurance}/{filterProblems}',
    [
        'uses' => 'ProcessEligibilityController@fromGoogleDriveDownloadedLocally',
        'as'   => 'process.eligibility.local.zip',
    ]
)->middleware(['auth', 'role:administrator']);
