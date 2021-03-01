<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Notifications;

use CircleLinkHealth\Core\Contracts\HasAttachment;
use CircleLinkHealth\Core\Contracts\NotificationAboutPatient;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Notifications\NoteForwarded;
use CircleLinkHealth\SharedModels\Notifications\PatientUnsuccessfulCallNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notification;

class DuplicateNotificationChecker
{
    const CONFIG_KEY                         = 'types_to_check_for_duplicate_notifications';
    const DEFAULT_NOTIFICATIONS_TO_CHECK_FOR = [
        NoteForwarded::class,
        PatientUnsuccessfulCallNotification::class,
    ];

    public static function getTypesToCheckFor()
    {
        $config = AppConfig::pull(self::CONFIG_KEY, null);
        if ( ! $config) {
            return self::DEFAULT_NOTIFICATIONS_TO_CHECK_FOR;
        }

        return explode(',', $config);
    }

    public static function hasAlreadySentNotification($notifiable, Notification $notification): bool
    {
        if ( ! isset($notifiable->id)) {
            return false;
        }

        $notificationType = get_class($notification);
        $checkForTypes    = self::getTypesToCheckFor();
        if ( ! in_array('*', $checkForTypes) && ! in_array($notificationType, $checkForTypes)) {
            return false;
        }

        $notifiableType = get_class($notifiable);
        $hasAlreadySent = self::queryExistingNotifications($notification)
            ->where('notifiable_id', '=', $notifiable->id)
            ->where('notifiable_type', '=', $notifiableType)
            ->exists();

        if ($hasAlreadySent) {
            return true;
        }

        $userIds = collect();
        if (Practice::class === $notifiableType) {
            $userIds = User::ofPractice($notifiable->id)
                ->pluck('id');
        } elseif (Location::class === $notifiableType) {
            $userIds = User::whereHas('locations', fn ($q) => $q->where('locations.id', '=', $notifiable->id))
                ->pluck('id');
        }

        if ($userIds->isNotEmpty()) {
            $alreadySentCount = self::queryExistingNotifications($notification)
                ->where('notifiable_id', 'in', $userIds)
                ->where('notifiable_type', '=', User::class)
                ->count();

            if ($alreadySentCount >= $userIds->count()) {
                return true;
            }
        }

        return false;
    }

    private static function queryExistingNotifications(Notification $notification): Builder
    {
        $tenMinutesAgo    = now()->subMinutes(10);
        $notificationType = get_class($notification);

        return DatabaseNotification::where('created_at', '>', $tenMinutesAgo)
            ->where('type', '=', $notificationType)
            ->when($notification instanceof HasAttachment, function ($q) use ($notification) {
                $attachment = $notification->getAttachment();
                if ( ! $attachment) {
                    return $q->whereNull('attachment_id');
                }

                return $q->where('attachment_id', $attachment->id)
                    ->where('attachment_type', get_class($attachment));
            })
            ->when($notification instanceof NotificationAboutPatient, function ($q) use ($notification) {
                $patientId = $notification->notificationAboutPatientWithUserId();
                if ( ! $patientId) {
                    return $q->whereNull('patient_id');
                }

                return $q->where('patient_id', $patientId);
            });
    }
}
