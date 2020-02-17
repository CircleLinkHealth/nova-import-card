<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Events\CallIsReadyForAttestedProblemsAttachment;
use App\Events\CarePlanWasApproved;
use App\Events\NoteFinalSaved;
use App\Events\PdfableCreated;
use App\Events\UpdateUserLoginInfo;
use App\Events\UpdateUserSessionInfo;
use App\Listeners\AttachAttestedProblemsToCall;
use App\Listeners\CheckBeforeSendMessageListener;
use App\Listeners\CreateAndHandlePdfReport;
use App\Listeners\ForwardNote;
use App\Listeners\LogFailedNotification;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use App\Listeners\PatientContactWindowUpdated;
use App\Listeners\UpdateCarePlanStatus;
use App\Listeners\UserLoggedOut;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use App\Listeners\NotifySlackChannel;
use App\Listeners\UPG0506WorkflowListener;
use CircleLinkHealth\Customer\Events\PatientContactWindowUpdatedEvent;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Events\NotificationFailed;

class CpmEventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Login::class => [
            UpdateUserLoginInfo::class,
            LogSuccessfulLogin::class,
        ],
        Authenticated::class => [
            UpdateUserSessionInfo::class,
        ],
        CarePlanWasApproved::class => [
            UpdateCarePlanStatus::class,
        ],
        PdfableCreated::class => [
            CreateAndHandlePdfReport::class,
        ],
        Logout::class => [
            UserLoggedOut::class,
            LogSuccessfulLogout::class,
        ],
        MessageSending::class => [
            CheckBeforeSendMessageListener::class,
        ],
        NoteFinalSaved::class => [
            ForwardNote::class,
        ],
        NotificationFailed::class => [
            LogFailedNotification::class,
        ],
        PatientContactWindowUpdatedEvent::class => [
            PatientContactWindowUpdated::class,
        ],
        CallIsReadyForAttestedProblemsAttachment::class => [
            AttachAttestedProblemsToCall::class,
        ],
        DirectMailMessageReceived::class => [
            UPG0506WorkflowListener::class,
            NotifySlackChannel::class,
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function boot()
    {
        parent::boot();
    }
}
