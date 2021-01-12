<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\NotificationMailUnsubscribe;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

trait NotificationSubscribable
{
    /**
     * @param $unsubscription
     *
     * @return mixed
     */
    public function checkIfWasRecentlyCreated($unsubscription)
    {
        return $unsubscription->wasRecentlyCreated;
    }

    /**
     * Returns TRUE if  entry doesn't exists or is softDeleted.
     *
     * @param $notificationType
     * @param $userId
     *
     * @return bool
     */
    public function checkSubscriptions($notificationType, $userId)
    {
        return NotificationMailUnsubscribe::where('user_id', $userId)
            ->where('notification_type', $notificationType)
            ->doesntExist();
    }

    /**
     * @param $activityType
     *
     * @return string
     */
    public function createUnsubscribeUrl($activityType)
    {
        return URL::temporarySignedRoute('unsubscribe.notifications.mail', now()->addDays(10), ['activityType' => $activityType]);
    }

    /**
     * @param $email
     *
     * @return array
     */
    public function dataForClhEmail($email)
    {
        return [
            'senderName'     => $this->senderName(),
            'date'           => $this->dateForMail(),
            'activityType'   => $this->descriptionForMail(),
            'notifiableMail' => $email,
        ];
    }

    /**
     * @param $userId
     * @param $activityType
     *
     * @return Model|NotificationMailUnsubscribe
     */
    public function getOrCreateUnsubscription($userId, $activityType)
    {
        return NotificationMailUnsubscribe::withTrashed()->firstOrCreate(
            ['user_id' => $userId, 'notification_type' => $activityType],
            ['unsubscribed_at' => Carbon::parse(now())->toDate()]
        );
    }

    /**
     * @param $notificationType
     *
     * @throws \Exception
     */
    public function subscribeToNotification($notificationType)
    {
        $unSubscription = NotificationMailUnsubscribe::where('user_id', auth()->id())
            ->where('notification_type', $notificationType)
            ->first();

        if ( ! empty($unSubscription)) {
            $unSubscription->delete();
        }
    }

    public function updateSubscription(NotificationMailUnsubscribe $unsubscription)
    {
        $unsubscription->restore();
        $unsubscription->update(['unsubscribed_at' => Carbon::parse(now())->toDate()]);
    }
}
