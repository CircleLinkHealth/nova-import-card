<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Contracts\ReportFormatter;
use App\Formatters\WebixFormatter;
use App\Notifications\Channels\FaxChannel;
use App\Notifications\NotificationStrategies\SendsNotification;
use App\Services\AWV\DirectPatientDocument;
use App\Services\AWV\EmailPatientDocument;
use App\Services\AWV\FaxPatientDocument;
use Carbon\Carbon;
use CircleLinkHealth\Core\Notifications\Channels\CustomMailChannel;
use CircleLinkHealth\Core\Notifications\Channels\CustomTwilioChannel;
use CircleLinkHealth\Core\Providers\GoogleDriveServiceProvider;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Horizon\Horizon;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Queue;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Schema::defaultStringLength(255);

        /*
         * If the current date is the 31st of the month, Carbon::now()->subMonth() will go back to the 31st of the previous month.
         * If the previous month does not have 31 days, `$startDate = Carbon::now()->subMonth()->startOfMonth();` jumps to the first day of the current month(!?!).
         *
         * To fix this, we could use: `Carbon::useMonthsOverflow(false);`
         *
         * More info here: briannesbitt/Carbon#37, briannesbitt/Carbon#710
         */
        Carbon::useMonthsOverflow(false);

        //need to set trusted hosts before request is passed on to our routers
        Request::setTrustedHosts(config('trustedhosts.hosts'));

        /*
         * If PHP cannot write in default /tmp directory, SwiftMailer fails.
         * To overcome this, set SwiftMailer to use storage_path('tmp').
         */
        if (class_exists('Swift_Preferences')) {
            \Swift_Preferences::getInstance()->setTempDir(storage_path('tmp'));
        } else {
            \Log::warning('Class Swift_Preferences does not exist.');
        }

        Horizon::auth(
            function ($request) {
                return optional(auth()->user())->hasRole(['administrator', 'developer']);
            }
        );

        Queue::looping(
            function () {
                //Rollback any transactions that were left open by a previously failed job
                while (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
            }
        );

        QueryBuilder::macro(
            'toRawSql',
            function () {
                return array_reduce(
                    $this->getBindings(),
                    function ($sql, $binding) {
                        return preg_replace(
                            '/\?/',
                            is_numeric($binding)
                                ? $binding
                                : "'".$binding."'",
                            $sql,
                            1
                        );
                    },
                    $this->toSql()
                );
            }
        );

        EloquentBuilder::macro(
            'toRawSql',
            function () {
                return $this->getQuery()->toRawSql();
            }
        );

        /** @var ChannelManager $cm */
        $cm = $this->app->make(ChannelManager::class);
        $cm->extend('phaxio', function (Application $app) {
            return $app->make(FaxChannel::class);
        });
        $cm->extend('twilio', function (Application $app) {
            return $app->make(CustomTwilioChannel::class);
        });
        $cm->extend('mail', function (Application $app) {
            return $app->make(CustomMailChannel::class);
        });

        if ($this->app->runningUnitTests() && \Config::get('database.default')) {
            \Illuminate\Database\Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
                return new class($connection, $database, $prefix, $config) extends SQLiteConnection {
                    public function getSchemaBuilder()
                    {
                        if (null === $this->schemaGrammar) {
                            $this->useDefaultSchemaGrammar();
                        }

                        return new class($this) extends SQLiteBuilder {
                            protected function createBlueprint($table, \Closure $callback = null)
                            {
                                return new class($table, $callback) extends Blueprint {
                                    public function dropForeign($index)
                                    {
                                        return new Fluent();
                                    }
                                };
                            }
                        };
                    }
                };
            });
        }
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     */
    public function register()
    {
        // Excel Package Importing Config
        // Format input array keys to be all lower-case and sluggified
        HeadingRowFormatter::extend('custom', function ($value) {
            return strtolower(Str::slug($value));
        });

        $this->app->register(\Maatwebsite\Excel\ExcelServiceProvider::class);
        $this->app->register(\Yajra\DataTables\DataTablesServiceProvider::class);

        $this->app->bind(
            SendsNotification::class,
            function ($app, $args) {
                switch ($args['channel']) {
                    case 'email':
                        return new EmailPatientDocument($args['patient'], $args['media'], $args['input']);
                        break;
                    case 'direct':
                        return new DirectPatientDocument($args['patient'], $args['media'], $args['input']);
                        break;
                    case 'fax':
                        return new FaxPatientDocument($args['patient'], $args['media'], $args['input']);
                        break;
                    default:
                        throw new \Exception('Channel Supplied from Patient AWV Care Docs Page is invalid.');
                }
            }
        );

        $this->app->bind(
            ReportFormatter::class,
            WebixFormatter::class
        );

        $this->app->register(\Laracasts\Utilities\JavaScript\JavaScriptServiceProvider::class);
        $this->app->register(\Barryvdh\Snappy\ServiceProvider::class);

        $this->app->register(\jeremykenedy\Slack\Laravel\ServiceProvider::class);
        $this->app->register(EmailArrayValidatorServiceProvider::class);
        $this->app->register(\Propaganistas\LaravelPhone\PhoneServiceProvider::class);
        $this->app->register(GoogleDriveServiceProvider::class);
        $this->app->register(\LynX39\LaraPdfMerger\PdfMergerServiceProvider::class);

//        Auth::provider('enrollmentLogin', function ($app, array $config) {
//            return new AutoEnrollmentLoginProvider($app['hash'], $config['model']);
//        });
    }
}
