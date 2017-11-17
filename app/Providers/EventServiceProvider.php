<?php namespace App\Providers;

use App\Events\CarePlanWasApproved;
use App\Events\PdfableCreated;
use App\Events\UpdateUserLoginInfo;
use App\Listeners\ClosePatientSession;
use App\Listeners\CreateAndHandlePdfReport;
use App\Listeners\UpdateCarePlanStatus;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Login::class               => [
            UpdateUserLoginInfo::class,
        ],
        CarePlanWasApproved::class => [
            UpdateCarePlanStatus::class,
        ],
        PdfableCreated::class      => [
            CreateAndHandlePdfReport::class,
        ],
        Logout::class              => [
            ClosePatientSession::class,
        ],
        MessageSending::class      => [

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
