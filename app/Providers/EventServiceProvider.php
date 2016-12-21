<?php namespace App\Providers;

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
        'App\Events\CarePlanWasApproved'        => [
            'App\Listeners\UpdateCarePlanStatus',
            'App\Listeners\CreateAprimaPdfCarePlan',
            'App\Listeners\CreateAthenaPdfCarePlan',
        ],
        'App\Events\NoteWasForwarded'           => [
            'App\Listeners\HandleCreatedNote',
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
