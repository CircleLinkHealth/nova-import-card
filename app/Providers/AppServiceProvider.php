<?php namespace App\Providers;

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
use App\Services\NoteService;
use App\Services\PatientService;
use App\Services\CPM\CpmProblemService;
use App\Services\CCD\CcdProblemService;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;

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
        //Bind database notification classes to local
        $this->app->bind(DatabaseChannel::class, \App\Notifications\Channels\DatabaseChannel::class);
        $this->app->bind(DatabaseNotification::class, \App\DatabaseNotification::class);
        $this->app->bind(HasDatabaseNotifications::class, \App\Notifications\HasDatabaseNotifications::class);
        $this->app->bind(Notifiable::class, \App\Notifications\Notifiable::class);

        if ($this->app->environment('local', 'testing', 'staging')) {
            $this->app->register(DuskServiceProvider::class);
        }

        $this->app->alias('bugsnag.multi', \Illuminate\Contracts\Logging\Log::class);
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);

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
            UserRepositoryEloquent::class
        );

        $this->app->bind(
            ReportFormatter::class,
            WebixFormatter::class
        );

        $this->app->bind(PatientService::class, function () {
            return new PatientService(new \App\Repositories\PatientRepository(app()), new \App\Repositories\UserRepositoryEloquent(app()));
        });
        
        $this->app->bind(CpmInstructionService::class, function () {
            return new CpmInstructionService(new \App\Repositories\CpmInstructionRepository(app()), new \App\Repositories\UserRepositoryEloquent(app()));
        });
        
        $this->app->bind(CpmProblemService::class, function () {
            return new CpmProblemService(new \App\Repositories\CpmProblemRepository(app()), new \App\Repositories\UserRepositoryEloquent(app()));
        });
        
        $this->app->bind(\App\Services\CCD\CcdProblemService::class, function () {
            return new \App\Services\CCD\CcdProblemService(new \App\Repositories\CcdProblemRepository(app()), new \App\Repositories\UserRepositoryEloquent(app()));
        });

        $this->app->bind(WebixFormatter::class, function(){
            return new WebixFormatter(new NoteService());
        });

        if ($this->app->environment('local')) {
            $this->app->register('Orangehill\Iseed\IseedServiceProvider');
        }
    }
}
