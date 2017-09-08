<?php namespace App\Providers;

use App\AppConfig;
use App\CarePlan;
use App\Contracts\Efax;
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
use App\Repositories\UserRepositoryEloquent;
use App\Services\Phaxio\PhaxioService;
use Illuminate\Support\ServiceProvider;
use View;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (\Schema::hasTable((new AppConfig)->getTable())) {
            $appConfigs = AppConfig::all();
            $adminStylesheet = $appConfigs->where('config_key', 'admin_stylesheet')->first();
            view()->share('app_config_admin_stylesheet', 'admin-bootswatch-default.css');
            if ($adminStylesheet) {
                view()->share('app_config_admin_stylesheet', $adminStylesheet->config_value);
            }
        }
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
        $this->app->alias('bugsnag.logger', \Illuminate\Contracts\Logging\Log::class);
        $this->app->alias('bugsnag.logger', \Psr\Log\LoggerInterface::class);

        $this->app->bind(
            'Illuminate\Contracts\Auth\Registrar',
            'App\Services\Registrar'
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
            Efax::class,
            PhaxioService::class
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
            UserRepositoryEloquent::class
        );

        $this->app->bind(
            ReportFormatter::class,
            WebixFormatter::class
        );

        if ($this->app->environment('local')) {
            $this->app->register('Orangehill\Iseed\IseedServiceProvider');
        }
    }

}
