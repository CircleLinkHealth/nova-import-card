<?php


namespace CircleLinkHealth\SelfEnrollment\Providers;

use CircleLinkHealth\Core\Listeners\LogFailedNotification;
use CircleLinkHealth\Core\Listeners\LogMailSmtpId;
use CircleLinkHealth\Core\Listeners\LogSentMailNotification;
use CircleLinkHealth\Core\Listeners\LogSentNotification;
use CircleLinkHealth\Core\Listeners\CheckBeforeSendMessageListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\MailManager;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;


class EventServiceProvider extends  ServiceProvider
{

    protected $listen = [
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
    ];
}