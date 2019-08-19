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
    public $service;

    /**
     * NotificationController constructor.
     *
     * @param NotificationService $notificationService
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
        $userNotifications = $user->notifications;
        $notifications     = $this->service->prepareNotifications($userNotifications);
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
