<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 18/04/2019
 * Time: 10:57 AM
 */

return [

    'twilio' => [
        'enabled'          => env('TWILIO_ENABLED', false),
        'sid'              => env('TWILIO_SID', 'somerandomstring'),
        'token'            => env('TWILIO_TOKEN', 'somerandomstring'),
        'from'             => env('TWILIO_FROM', 'somerandomstring'),
        'twiml-app-sid'    => env('TWIML_APP_SID', 'somerandomstring'),
        'allow-conference' => env('TWIML_ALLOW_CONFERENCE', false),
        'allow-recording'  => env('TWIML_ALLOW_RECORDING', false),
    ],

];
