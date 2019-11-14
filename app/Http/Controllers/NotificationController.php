<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * @var NotificationService
     */
    public $service;

    /**
     * NotificationController constructor.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->service = $notificationService;
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $notifications                = $this->service->getDropdownNotifications();
        $allUnreadNotificationsCount  = $this->service->getDropdownNotificationsCount();
        $notificationsWithElapsedTime = $this->service->prepareNotifications($notifications);

        return response()->json([
            'notifications' => $notificationsWithElapsedTime,
            'totalCount'    => $allUnreadNotificationsCount,
        ]);
    }

    /**
     * @param $receiverId
     * @param $notificationId
     */
    public function markNotificationAsRead($receiverId, $notificationId)
    {
        $this->service->markAsRead($receiverId, $notificationId);
    }

    /**
     * @return Factory|View
     */
    public function seeAllNotifications()
    {
        $notifications = $this->service->getAllUserNotifications();

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
