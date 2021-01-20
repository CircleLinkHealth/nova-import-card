<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Listeners\AddPatientConsentNote;
use App\Listeners\AttachUPG0506CarePlanToPatientUser;
use App\Listeners\AutoApproveCarePlan;
use App\Listeners\ChangeOrApproveCareplanResponseListener;
use App\Listeners\CheckBeforeSendMessageListener;
use App\Listeners\CreateAndHandlePdfReport;
use App\Listeners\ForwardApprovedCarePlanToPractice;
use App\Listeners\LogScheduledTask;
use App\Listeners\LogSuccessfulLogout;
use App\Listeners\NotifyPatientOfCarePlanApproval;
use App\Listeners\NotifySlackChannel;
use App\Listeners\PatientContactWindowUpdated;
use App\Listeners\RunComposerIde;
use App\Listeners\SendCarePlanForDMProviderApproval;
use App\Listeners\UpdateCarePlanStatus;
use App\Listeners\UpdateCcdaStatus;
use App\Listeners\UpdateUserLoginInfo;
use App\Listeners\UpdateUserSessionInfo;
use App\Listeners\UPG0506CcdaImporterListener;
use App\Listeners\UPG0506DirectMailListener;
use App\Listeners\UPG0506Handler;
use App\Listeners\UserLoggedOut;
use CircleLinkHealth\CcmBilling\Events\LocationServicesAttached;
use CircleLinkHealth\CcmBilling\Events\NurseAttestedToPatientProblems;
use CircleLinkHealth\CcmBilling\Events\PatientActivityCreated;
use CircleLinkHealth\CcmBilling\Events\PatientProblemsChanged;
use CircleLinkHealth\CcmBilling\Events\PatientSuccessfulCallCreated;
use CircleLinkHealth\CcmBilling\Listeners\CreateAttestationRecords;
use CircleLinkHealth\CcmBilling\Listeners\ProcessLocationPatientServices;
use CircleLinkHealth\CcmBilling\Listeners\ProcessLocationProblemServices;
use CircleLinkHealth\CcmBilling\Listeners\ProcessPatientServices;
use CircleLinkHealth\Core\Listeners\LogFailedNotification;
use CircleLinkHealth\Core\Listeners\LogMailSmtpId;
use CircleLinkHealth\Core\Listeners\LogSentMailNotification;
use CircleLinkHealth\Core\Listeners\LogSentNotification;
use CircleLinkHealth\Core\Listeners\PostmarkAddSmtpIdOnHeader;
use CircleLinkHealth\Core\Services\PhiMail\Events\DirectMailMessageReceived;
use CircleLinkHealth\Customer\Events\CarePlanWasApproved;
use CircleLinkHealth\Customer\Events\CarePlanWasProviderApproved;
use CircleLinkHealth\Customer\Events\CarePlanWasQAApproved;
use CircleLinkHealth\Customer\Events\CarePlanWasRNApproved;
use CircleLinkHealth\Customer\Events\PatientContactWindowUpdatedEvent;
use CircleLinkHealth\Customer\Events\PatientUserCreated;
use CircleLinkHealth\Customer\Events\PdfableCreated;
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
        NurseAttestedToPatientProblems::class => [
            CreateAttestationRecords::class,
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
        LocationServicesAttached::class => [
            ProcessLocationPatientServices::class,
            ProcessLocationProblemServices::class,
        ],
        PatientProblemsChanged::class => [
            ProcessPatientServices::class,
        ],
        PatientActivityCreated::class => [
            ProcessPatientServices::class,
        ],
        PatientSuccessfulCallCreated::class => [
            ProcessPatientServices::class,
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