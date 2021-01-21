<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::group([
    'prefix' => 'twilio',
], function () {
    Route::post('/sms/status', [
        'uses' => 'TwilioController@smsStatusCallback',
        'as'   => 'twilio.sms.status',
    ]);

    Route::post('/sms/inbound', [
        'uses' => 'TwilioController@smsInbound',
        'as'   => 'twilio.sms.inbound',
    ]);
});
