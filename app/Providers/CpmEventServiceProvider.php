<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Events\CallIsReadyForAttestedProblemsAttachment;
use App\Events\CarePlanWasApproved;
use App\Events\CarePlanWasProviderApproved;
use App\Events\CarePlanWasQAApproved;
use App\Events\CarePlanWasRNApproved;
use App\Events\NoteFinalSaved;
use App\Events\PatientUserCreated;
use App\Events\PdfableCreated;
use App\Events\UpdateUserLoginInfo;
use App\Events\UpdateUserSessionInfo;
use App\Listeners\AddPatientConsentNote;
use App\Listeners\AttachAttestedProblemsToCall;
use App\Listeners\AttachUPG0506CarePlanToPatientUser;
use App\Listeners\AutoApproveCarePlan;
use App\Listeners\ChangeOrApproveCareplanResponseListener;
use App\Listeners\CheckBeforeSendMessageListener;
use App\Listeners\CreateAndHandlePdfReport;
use App\Listeners\ForwardApprovedCarePlanToPractice;
use App\Listeners\ForwardNote;
use App\Listeners\LogScheduledTask;
use App\Listeners\LogSuccessfulLogout;
use App\Listeners\NotifyPatientOfCarePlanApproval;
use App\Listeners\NotifySlackChannel;
use App\Listeners\PatientContactWindowUpdated;
use App\Listeners\RunComposerIde;
use App\Listeners\SendCarePlanForDMProviderApproval;
use App\Listeners\UpdateCarePlanStatus;
use App\Listeners\UpdateCcdaStatus;
use App\Listeners\UPG0506CcdaImporterListener;
use App\Listeners\UPG0506DirectMailListener;
use App\Listeners\UPG0506Handler;
use App\Listeners\UserLoggedOut;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use CircleLinkHealth\Core\Listeners\LogFailedNotification;
use CircleLinkHealth\Core\Listeners\LogMailSmtpId;
use CircleLinkHealth\Core\Listeners\LogSentMailNotification;
use CircleLinkHealth\Core\Listeners\LogSentNotification;
use CircleLinkHealth\Core\Listeners\PostmarkAddSmtpIdOnHeader;
use CircleLinkHealth\Customer\Events\PatientContactWindowUpdatedEvent;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Events\CcdaImported;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\MailManager;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use InvalidArgumentException;

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
            LogMailSmtpId::class, //this needs to be first
            CheckBeforeSendMessageListener::class,
        ],
        NoteFinalSaved::class => [
            ForwardNote::class,
        ],
        MessageSent::class => [
            LogSentMailNotification::class,
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
            AddPatientConsentNote::class,
        ],
        CarePlanWasRNApproved::class => [
            AutoApproveCarePlan::class,
            UPG0506Handler::class, //auto approve for UPG0506 - why not in AutoApproveCarePlan then?
            NotifyPatientOfCarePlanApproval::class,
            SendCarePlanForDMProviderApproval::class,
        ],
        CarePlanWasProviderApproved::class => [
            ForwardApprovedCarePlanToPractice::class,
            NotifyPatientOfCarePlanApproval::class,
        ],
        ScheduledTaskStarting::class => [
            LogScheduledTask::class,
        ],
        ScheduledTaskFinished::class => [
            LogScheduledTask::class,
        ],
        MigrationsEnded::class => [
            RunComposerIde::class,
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

        /** @var MailManager $manager */
        $manager = app(MailManager::class);
        try {
            $pmMailer = $manager->mailer('postmark');
            if ($pmMailer) {
                $pmMailer->getSwiftMailer()->getTransport()->registerPlugin(new PostmarkAddSmtpIdOnHeader());
            }
        } catch (InvalidArgumentException $e) {
            // no need to do anything. we do not have config for postmark mailer
        }
    }
}
