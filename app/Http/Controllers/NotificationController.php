<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    const LIMIT_DATE        = '2019-10-01';
    const NOTIFICATION_TYPE = 'App\Notifications\AddendumCreated'; //temporary solution

    /**
     * @var NotificationService
     */
    public $service;
    /**
     * @var Carbon
     */
    private $notificationsLimitDate;

    /**
     * NotificationController constructor.
     *
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->service                = $notificationService;
        $this->notificationsLimitDate = Carbon::parse(self::LIMIT_DATE)->toDateTimeString();
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $notifications                = $this->service->getDropdownNotifications($this->notificationsLimitDate);
        $allUnreadNotificationsCount  = $this->service->getDropdownNotificationsCount($this->notificationsLimitDate);
        $notificationsWithElapsedTime = $this->service->prepareNotifications($notifications);

        return response()->json([
            'notifications' => $notificationsWithElapsedTime,
            'totalCount'    => $allUnreadNotificationsCount,
        ]);
    }

    /**
     * @param $receiverId
     * @param $attachmentId
     */
    public function markNotificationAsRead($receiverId, $attachmentId)
    {
        $this->service->markAsRead($receiverId, $attachmentId);
    }

    /**
     * @return Factory|View
     */
    public function seeAllNotifications()
    {
        $user              = auth()->user();
        $userNotifications = $user->notifications
            ->where('created_at', '>=', $this->notificationsLimitDate)
            ->where('type', self::NOTIFICATION_TYPE); //Lets keep this here since we show only addendums for now
        $notifications = $this->service->prepareNotifications($userNotifications);
        //@todo: pagination needed
        return view('notifications.seeAllNotifications', compact('notifications'));
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function showPusherNotification($id)
    {
        $notification    = $this->service->getPusherNotificationData($id);
        $createdDateTime = $this->service->notificationCreatedAt($notification);
        $this->service->addElapsedTime($notification, $createdDateTime);

        return response()->json($notification);
    }
}
