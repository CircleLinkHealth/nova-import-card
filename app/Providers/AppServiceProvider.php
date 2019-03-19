<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Contracts\HtmlToPdfService;
use App\Contracts\ReportFormatter;
use App\Contracts\Repositories\ActivityRepository;
use App\Contracts\Repositories\AprimaCcdApiRepository;
use App\Contracts\Repositories\CcdaRepository;
use App\Contracts\Repositories\CcdaRequestRepository;
use App\Contracts\Repositories\CcmTimeApiLogRepository;
use App\Contracts\Repositories\InviteRepository;
use App\Contracts\Repositories\LocationRepository;
use App\Contracts\Repositories\PracticeRepository;
use App\Contracts\Repositories\UserRepository;
use App\Formatters\WebixFormatter;
use App\Repositories\ActivityRepositoryEloquent;
use App\Repositories\AprimaCcdApiRepositoryEloquent;
use App\Repositories\CcdaRepositoryEloquent;
use App\Repositories\CcdaRequestRepositoryEloquent;
use App\Repositories\CcmTimeApiLogRepositoryEloquent;
use App\Repositories\InviteRepositoryEloquent;
use App\Repositories\LocationRepositoryEloquent;
use App\Repositories\PracticeRepositoryEloquent;
use App\Repositories\PrettusUserRepositoryEloquent;
use App\Services\SnappyPdfWrapper;
use DB;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;
use Laravel\Horizon\Horizon;
use Orangehill\Iseed\IseedServiceProvider;
use Queue;
use Way\Generators\GeneratorsServiceProvider;
use Xethron\MigrationsGenerator\MigrationsGeneratorServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        //need to set trusted hosts before request is passed on to our routers
        Request::setTrustedHosts(config('trustedhosts.hosts'));

        Horizon::auth(
            function ($request) {
                return optional(auth()->user())->isAdmin();
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
    
        QueryBuilder::macro('toRawSql', function(){
            return array_reduce($this->getBindings(), function($sql, $binding){
                return preg_replace('/\?/', is_numeric($binding) ? $binding : "'".$binding."'" , $sql, 1);
            }, $this->toSql());
        });
    
        EloquentBuilder::macro('toRawSql', function(){
            return ($this->getQuery()->toRawSql());
        });
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
        //Bind database notification classes to local
        $this->app->bind(DatabaseChannel::class, \App\Notifications\Channels\DatabaseChannel::class);
        $this->app->bind(DatabaseNotification::class, \App\DatabaseNotification::class);
        $this->app->bind(HasDatabaseNotifications::class, \App\Notifications\HasDatabaseNotifications::class);
        $this->app->bind(Notifiable::class, \App\Notifications\Notifiable::class);
        $this->app->bind(
            HtmlToPdfService::class,
            function () {
                return $this->app->make(SnappyPdfWrapper::class);
            }
        );

        $this->app->bind(
            ActivityRepository::class,
            ActivityRepositoryEloquent::class
        );

        $this->app->bind(
            CcdaRepository::class,
            CcdaRepositoryEloquent::class
        );

        $this->app->bind(
            CcdaRequestRepository::class,
            CcdaRequestRepositoryEloquent::class
        );

        $this->app->bind(
            CcmTimeApiLogRepository::class,
            CcmTimeApiLogRepositoryEloquent::class
        );

        $this->app->bind(
            AprimaCcdApiRepository::class,
            AprimaCcdApiRepositoryEloquent::class
        );

        $this->app->bind(
            InviteRepository::class,
            InviteRepositoryEloquent::class
        );

        $this->app->bind(
            LocationRepository::class,
            LocationRepositoryEloquent::class
        );

        $this->app->bind(
            PracticeRepository::class,
            PracticeRepositoryEloquent::class
        );

        $this->app->bind(
            \App\CLH\Contracts\Repositories\UserRepository::class,
            \App\CLH\Repositories\UserRepository::class
        );

        $this->app->bind(
            UserRepository::class,
            PrettusUserRepositoryEloquent::class
        );

        $this->app->bind(
            ReportFormatter::class,
            WebixFormatter::class
        );

        if ($this->app->environment('local')) {
            $this->app->register(IseedServiceProvider::class);
            $this->app->register(GeneratorsServiceProvider::class);
            $this->app->register(MigrationsGeneratorServiceProvider::class);
            $this->app->register(DuskServiceProvider::class);
        }
    }
}
