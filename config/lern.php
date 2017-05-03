<?php

return [


    'record' => [
        'table'       => 'exceptions',
        'collect'     => [
            'method'      => true,
            //When true it will collect GET, POST, DELETE, PUT, etc...
            'data'        => true,
            //When true it will collect Input data
            'status_code' => true,
            'user_id'     => true,
            'url'         => true,
        ],

        /**
         * When record.collect.data is true, this will exclude certain data keys recursively
         */
        'excludeKeys' => [
            'password',
        ],
    ],

    'notify' => [
        /**
         * The view file to use
         */
        'view'                       => 'exceptions.default',

        /**
         * The default name of the monolog logger channel
         */
        'channel'                    => 'Tylercd100\LERN',

        /**
         * The log level to use when notifying
         */
        'log_level'                  => 'emergency',
        //Options are: debug, info, notice, warning, error, critical, alert, emergency.

        /**
         * When using the default message body this will also include the stack trace
         */
        'includeExceptionStackTrace' => true,

        /**
         * mail, pushover, slack, etc...
         */
        'drivers'                    => [
            'slack',
        ],

        /**
         * Mail settings
         */
        'mail'                       => [
            'to'   => 'mantoniou@circlelinkhealth.com',
            'from' => 'exceptions@circlelinkhealth.com',
            'smtp' => true,
        ],

        /**
         * Mailgun settings
         */
        'mailgun'                    => [
            'to'     => env('MAILGUN_TO'),
            'from'   => env('MAILGUN_FROM'),
            'token'  => env('MAILGUN_APP_TOKEN'),
            'domain' => env('MAILGUN_DOMAIN'),
        ],

        /**
         * Pushover settings
         */
        'pushover'                   => [
            'token' => env('PUSHOVER_APP_TOKEN'),
            'users' => env('PUSHOVER_USER_KEY'),
            'sound' => env('PUSHOVER_SOUND_ERROR', 'siren'),
            // https://pushover.net/api#sounds
        ],

        /**
         * Slack settings
         */
        'slack'                      => [
            //https://api.slack.com/web#auth
            'token' => env('SLACK_APP_TOKEN', ''),

            //Dont forget the '#'
            'channel' => env('SLACK_EXCEPTIONS_CHANNEL', '#exceptions'),

            //The 'from' name
            'username' => env('SLACK_USERNAME', 'LERN'),

            'webhook'  => env('SLACK_WEBHOOK',
                'https://hooks.slack.com/services/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'),
        ],

        /**
         * HipChat settings
         */
        'hipchat'                    => [
            'token'  => env('HIPCHAT_APP_TOKEN'),
            'room'   => 'room',
            'name'   => 'name',
            'notify' => true,
        ],

        /**
         * Flowdock settings
         */
        'flowdock'                   => [
            'token' => env('FLOWDOCK_APP_TOKEN'),
        ],

        /**
         * Fleephook settings
         */
        'fleephook'                  => [
            'token' => env('FLEEPHOOK_APP_TOKEN'),
        ],

        /**
         * Plivo settings
         */
        'plivo'                      => [
            'auth_id' => env('PLIVO_AUTH_ID'),
            'token'   => env('PLIVO_AUTH_TOKEN'),
            'to'      => env('PLIVO_TO'),
            'from'    => env('PLIVO_FROM'),
        ],

        /**
         * Twilio settings
         */
        'twilio'                     => [
            'sid'    => env('TWILIO_AUTH_SID'),
            'secret' => env('TWILIO_AUTH_SECRET'),
            'to'     => env('TWILIO_TO'),
            'from'   => env('TWILIO_FROM'),
        ],

        /**
         * Raven settings
         */
        'raven'                      => [
            'dsn' => env('RAVEN_DSN'),
        ],
    ],

];
