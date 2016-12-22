<?php namespace App\Providers;

use App\Events\CarePlanWasApproved;
use App\Events\PdfableCreated;
use App\Listeners\CreateAndHandlePdfReport;
use App\Listeners\CreateAprimaPdfCarePlan;
use App\Listeners\CreateAthenaPdfCarePlan;
use App\Listeners\UpdateCarePlanStatus;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Auth\Events\Login'          => [
            'App\Events\UpdateUserLoginInfo',
        ],
        CarePlanWasApproved::class              => [
            UpdateCarePlanStatus::class,
            CreateAprimaPdfCarePlan::class,
            CreateAthenaPdfCarePlan::class,
        ],
        PdfableCreated::class                   => [
            CreateAndHandlePdfReport::class,
        ],
        'Illuminate\Auth\Events\Logout'         => [
            'App\Listeners\ClosePatientSession',
        ],
        'Illuminate\Mail\Events\MessageSending' => [
            'App\Listeners\LogSentMessage',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }

}
