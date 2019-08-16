<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Contracts\ReportFormatter;
use App\Formatters\WebixFormatter;
use App\Services\SnappyPdfWrapper;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Orangehill\Iseed\IseedServiceProvider;
use Queue;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
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
            return strtolower(str_slug($value));
        });

        $this->app->register(\Maatwebsite\Excel\ExcelServiceProvider::class);
        $this->app->register(\Yajra\DataTables\DataTablesServiceProvider::class);

        if ($this->app->environment('local')) {
            DevelopmentServiceProvider::class;
        }

        $this->app->bind(
            ReportFormatter::class,
            WebixFormatter::class
        );

        $this->app->register(\Laracasts\Utilities\JavaScript\JavaScriptServiceProvider::class);
        $this->app->register(\Barryvdh\Snappy\ServiceProvider::class);

        $this->app->register(\jeremykenedy\Slack\Laravel\ServiceProvider::class);
        $this->app->register(EmailArrayValidatorServiceProvider::class);
        $this->app->register(\Propaganistas\LaravelPhone\PhoneServiceProvider::class);
        $this->app->register(\Waavi\UrlShortener\UrlShortenerServiceProvider::class);
        $this->app->register(GoogleDriveServiceProvider::class);
        $this->app->register(\LynX39\LaraPdfMerger\PdfMergerServiceProvider::class);
    }
}
