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
use Illuminate\Notifications\Notification;

class DuplicateNotificationChecker
{
    const DEFAULT_NOTIFICATIONS_TO_CHECK_FOR = [
        NoteForwarded::class,
        PatientUnsuccessfulCallNotification::class,
    ];
    const MINUTES_CHECK_CONFIG_KEY = 'minutes_to_check_for_duplicate_notifications';
    const TYPES_CONFIG_KEY         = 'types_to_check_for_duplicate_notifications';

    public static function getMinutesToCheckSince(): int
    {
        $config = AppConfig::pull(self::MINUTES_CHECK_CONFIG_KEY, 10);

        return intval($config);
    }

    public static function getTypesToCheckFor(): array
    {
        $config = AppConfig::pull(self::TYPES_CONFIG_KEY, null);
        if ( ! $config) {
            return self::DEFAULT_NOTIFICATIONS_TO_CHECK_FOR;
        }

        return explode(',', $config);
    }

    public static function hasAlreadySentNotification($notifiable, Notification $notification, string $channel): bool
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
        $hasAlreadySent = self::getExistingNotificationsCount([$notifiable->id], $notifiableType, $notification, $channel);
        if ($hasAlreadySent > 0) {
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
            $alreadySentCount = self::getExistingNotificationsCount($userIds->toArray(), User::class, $notification, $channel);
            if ($alreadySentCount >= $userIds->count()) {
                return true;
            }
        }

        return false;
    }

    private static function getExistingNotificationsCount(array $notifiableIds, string $notifiableType, Notification $notification, string $channel): int
    {
        $tenMinutesAgo    = now()->subMinutes(self::getMinutesToCheckSince());
        $notificationType = get_class($notification);

        $notifications = DatabaseNotification::where('created_at', '>', $tenMinutesAgo)
            ->where('type', '=', $notificationType)
            // couldn't get this to work with $.status.CircleLinkHealth\Core\Notifications\Channels\DirectMailChannel
            // ->whereRaw("JSON_EXTRACT(`data`, '$.status.$channel') is not null")
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
            })
            ->whereIn('notifiable_id', $notifiableIds)
            ->where('notifiable_type', '=', $notifiableType)
            ->get();

        $count = 0;
        foreach ($notifications as $notification) {
            if ( ! $notification->data || ! isset($notification->data['status']) || ! isset($notification->data['status'][$channel])) {
                continue;
            }
            ++$count;
        }

        return $count;
    }
}
