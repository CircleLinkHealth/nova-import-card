<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Events\CallIsReadyForAttestedProblemsAttachment;
use App\Events\CarePlanWasApproved;
use App\Events\CarePlanWasProviderApproved;
use App\Events\CarePlanWasQAApproved;
use App\Events\NoteFinalSaved;
use App\Events\PatientUserCreated;
use App\Events\PdfableCreated;
use App\Events\UpdateUserLoginInfo;
use App\Events\UpdateUserSessionInfo;
use App\Listeners\AddPatientConsentNote;
use App\Listeners\AssignPatientToStandByNurse;
use App\Listeners\AttachAttestedProblemsToCall;
use App\Listeners\AttachUPG0506CarePlanToPatientUser;
use App\Listeners\AutoApproveCarePlan;
use App\Listeners\ChangeOrApproveCareplanResponseListener;
use App\Listeners\CheckBeforeSendMessageListener;
use App\Listeners\CreateAndHandlePdfReport;
use App\Listeners\ForwardApprovedCarePlanToPractice;
use App\Listeners\ForwardNote;
use App\Listeners\LogFailedNotification;
use App\Listeners\LogSentNotification;
use App\Listeners\LogSuccessfulLogout;
use App\Listeners\NotifyPatientOfCarePlanApproval;
use App\Listeners\NotifySlackChannel;
use App\Listeners\PatientContactWindowUpdated;
use App\Listeners\SendCarePlanForDMProviderApproval;
use App\Listeners\UpdateCarePlanStatus;
use App\Listeners\UpdateCcdaStatus;
use App\Listeners\UPG0506CcdaImporterListener;
use App\Listeners\UPG0506DirectMailListener;
use App\Listeners\UPG0506Handler;
use App\Listeners\UserLoggedOut;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use CircleLinkHealth\Customer\Events\PatientContactWindowUpdatedEvent;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Events\CcdaImported;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;

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
        ],
        Authenticated::class => [
            UpdateUserSessionInfo::class,
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
        NotificationSent::class => [
            LogSentNotification::class,
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
            UPG0506DirectMailListener::class,
            ChangeOrApproveCareplanResponseListener::class,
            NotifySlackChannel::class,
        ],
        CcdaImported::class => [
            UPG0506CcdaImporterListener::class,
        ],
        PatientUserCreated::class => [
            AttachUPG0506CarePlanToPatientUser::class,
        ],
        CarePlanWasApproved::class => [
            UpdateCarePlanStatus::class,
            UpdateCcdaStatus::class,
        ],
        CarePlanWasQAApproved::class => [
            AssignPatientToStandByNurse::class,
            AddPatientConsentNote::class,
            AutoApproveCarePlan::class,
            UPG0506Handler::class,
            SendCarePlanForDMProviderApproval::class,
            NotifyPatientOfCarePlanApproval::class,
        ],
        CarePlanWasProviderApproved::class => [
            ForwardApprovedCarePlanToPractice::class,
            NotifyPatientOfCarePlanApproval::class,
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
