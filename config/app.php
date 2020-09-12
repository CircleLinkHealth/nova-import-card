<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Providers\CpmArtisanServiceProvider;
use App\Providers\PrimaryNavComposer;
use CircleLinkHealth\CcdaParserProcessorPhp\Providers\CcdaParserProcessorProvider;
use CircleLinkHealth\Core\Providers\SmartCacheServiceProvider;
use CircleLinkHealth\Eligibility\Providers\EligibilityDeferrableServiceProvider;
use CircleLinkHealth\Eligibility\Providers\EligibilityServiceProvider;
use CircleLinkHealth\ImportPracticeStaffCsv\CardServiceProvider;
use CircleLinkHealth\NurseInvoices\Providers\NurseInvoicesServiceProvider;
use Illuminate\Support\Arr;

$appUrl = env('APP_URL', 'http://cpm.dev');

$appUrl = str_replace('${HEROKU_APP_NAME}', getenv('HEROKU_APP_NAME'), $appUrl);

return [
    /*
     * Configure the editor you want to use:
     * sublime
     * idea
     * phpstorm
     * emacs
     * macvim
     * vscode
     * atom
     */
    'editor' => env('IDE', 'phpstorm'),

    /*
   |--------------------------------------------------------------------------
   | Application Name
   |--------------------------------------------------------------------------
   |
   | This value is the name of your application. This value is used when the
   | framework needs to place the application's name in a notification or
   | any other location as required by the application or its packages.
   */

    'name' => 'CarePlan Manager',

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => $appUrl,

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'America/New_York',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY', 'SomeRandomString'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [
        // Jenssegers User Agent
        Jenssegers\Agent\AgentServiceProvider::class,

        // Laravel Framework Service Providers...
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        SmartCacheServiceProvider::class,
        //Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        \App\Providers\RouteServiceProvider::class,
        App\Providers\VaporUiServiceProvider::class,
        \App\Providers\AuthServiceProvider::class,
        \App\Providers\CpmEventServiceProvider::class,
        \App\Providers\ObserversServiceProvider::class,

        \App\Providers\NovaServiceProvider::class,

        \CircleLinkHealth\Raygun\Providers\RaygunServiceProvider::class,

        App\Providers\AppServiceProvider::class,
        App\Providers\AppDeferredServiceProvider::class,

        App\Providers\BroadcastServiceProvider::class,

        App\Providers\HtmlToPdfServiceProvider::class,
        App\Providers\DirectMailServiceProvider::class,
        App\Providers\FaxServiceProvider::class,
        App\Providers\TwilioClientServiceProvider::class,
        \Collective\Html\HtmlServiceProvider::class,

        App\View\Composers\ProviderUITimerComposer::class,
        App\View\Composers\FabComposer::class,
        App\View\Composers\SAAS\Admin\ManageInternalUser::class,
        App\Providers\EligibilityBatchViewComposerServiceProvider::class,
        PrimaryNavComposer::class,

        NurseInvoicesServiceProvider::class,
        EligibilityDeferrableServiceProvider::class,
        EligibilityServiceProvider::class,
        CardServiceProvider::class,
        CcdaParserProcessorProvider::class,
        CpmArtisanServiceProvider::class,
        \Circlelinkhealth\ClhNovaTheme\ThemeServiceProvider::class,

        Spatie\SlashCommand\SlashCommandServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [
        'App'          => Illuminate\Support\Facades\App::class,
        'Agent'        => Jenssegers\Agent\Facades\Agent::class,
        'Artisan'      => Illuminate\Support\Facades\Artisan::class,
        'Auth'         => Illuminate\Support\Facades\Auth::class,
        'Blade'        => Illuminate\Support\Facades\Blade::class,
        'Cache'        => Illuminate\Support\Facades\Cache::class,
        'Carbon'       => Carbon\Carbon::class,
        'Config'       => Illuminate\Support\Facades\Config::class,
        'Cookie'       => Illuminate\Support\Facades\Cookie::class,
        'Crypt'        => Illuminate\Support\Facades\Crypt::class,
        'DB'           => Illuminate\Support\Facades\DB::class,
        'Eloquent'     => Illuminate\Database\Eloquent\Model::class,
        'Event'        => Illuminate\Support\Facades\Event::class,
        'File'         => Illuminate\Support\Facades\File::class,
        'Gate'         => Illuminate\Support\Facades\Gate::class,
        'Hash'         => Illuminate\Support\Facades\Hash::class,
        'Lang'         => Illuminate\Support\Facades\Lang::class,
        'Log'          => Illuminate\Support\Facades\Log::class,
        'Notification' => CircleLinkHealth\Core\Facades\Notification::class,
        'Mail'         => Illuminate\Support\Facades\Mail::class,
        'Password'     => Illuminate\Support\Facades\Password::class,
        'Queue'        => Illuminate\Support\Facades\Queue::class,
        'Redirect'     => Illuminate\Support\Facades\Redirect::class,
        'RedisManager' => Illuminate\Support\Facades\Redis::class,
        'Request'      => Illuminate\Support\Facades\Request::class,
        'Response'     => Illuminate\Support\Facades\Response::class,
        'Route'        => Illuminate\Support\Facades\Route::class,
        'Schema'       => Illuminate\Support\Facades\Schema::class,
        'Session'      => Illuminate\Support\Facades\Session::class,
        'Storage'      => Illuminate\Support\Facades\Storage::class,
        'URL'          => Illuminate\Support\Facades\URL::class,
        'Validator'    => Illuminate\Support\Facades\Validator::class,
        'View'         => Illuminate\Support\Facades\View::class,

        'Inspiring' => Illuminate\Foundation\Inspiring::class,

        'DataTables'   => Yajra\DataTables\Facades\DataTables::class,
        'Form'         => \Collective\Html\FormFacade::class,
        'Html'         => \Collective\Html\HtmlFacade::class,
        'Image'        => \Barryvdh\Snappy\Facades\SnappyImage::class,
        'PdfMerger'    => LynX39\LaraPdfMerger\Facades\PdfMerger::class,
        'Slack'        => jeremykenedy\Slack\Laravel\Facade::class,
        'Swagger'      => L5Swagger\L5SwaggerServiceProvider::class,
        'UrlShortener' => Waavi\UrlShortener\Facades\UrlShortener::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | App Version Variable in .env
    |--------------------------------------------------------------------------
    |
    | Where to get the APP_VERSION from
    |
    |
    | Defaults to ''.
    |
    */
    'app_version' => env('APP_VERSION', ''),

    // Hide these variables from debug screens (Whoops, Raygun, etc)
    'debug_blacklist' => [
        '_COOKIE' => array_keys($_COOKIE),
        '_SERVER' => array_keys($_SERVER),
        '_ENV'    => Arr::except(array_keys($_ENV), [
            'APP_ENV',
            'SCOUT_DRIVER',
            'BROADCAST_DRIVER',
            'CACHE_DRIVER',
            'SESSION_DRIVER',
            'SESSION_DOMAIN',
            'TWO_FA_ENABLED',
        ]),
    ],
];
