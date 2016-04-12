<?php namespace App\Providers;

use App\Contracts\Repositories\ActivityRepository;
use App\Contracts\Repositories\CcmTimeApiLogRepository;
use App\Contracts\Repositories\DemographicsImportRepository;
use App\Contracts\Repositories\UserRepository;
use App\Repositories\ActivityRepositoryEloquent;
use App\Repositories\CcmTimeApiLogRepositoryEloquent;
use App\Repositories\DemographicsImportRepositoryEloquent;
use App\Repositories\UserRepositoryEloquent;
use Illuminate\Support\ServiceProvider;
use Prettus\Repository\Contracts\RepositoryInterface;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'Illuminate\Contracts\Auth\Registrar',
            'App\Services\Registrar'
        );

        $this->app->bind(
            ActivityRepository::class,
            ActivityRepositoryEloquent::class
        );

        $this->app->bind(
            CcmTimeApiLogRepository::class,
            CcmTimeApiLogRepositoryEloquent::class
        );

        $this->app->bind(
            DemographicsImportRepository::class,
            DemographicsImportRepositoryEloquent::class
        );

        $this->app->bind(
            '\App\CLH\Contracts\Repositories\UserRepository',
            '\App\CLH\Repositories\UserRepository'
        );

        $this->app->bind(
            UserRepository::class,
            UserRepositoryEloquent::class
        );

        if ( $this->app->environment( 'local' ) ) {
            $this->app->register( 'Orangehill\Iseed\IseedServiceProvider' );
        }
    }

}
