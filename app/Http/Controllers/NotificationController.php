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
            'notifications' => $notificationsWithElapsedTime->values(),
            'totalCount'    => $allUnreadNotificationsCount,
        ]);
    }

    /**
     * @param $notificationId
     */
    public function markNotificationAsRead($notificationId)
    {
        $this->service->markAsRead($notificationId);
    }

    /**
     * @return Factory|View
     */
    public function seeAllNotifications()
    {
        return view('notifications.seeAllNotifications');
    }

    /**
     * @param $page
     * @param $resultsPerPage
     *
     * @return JsonResponse
     */
    public function seeAllNotificationsPaginated($page, $resultsPerPage)
    {
        $notificationsPerPage = ! empty($resultsPerPage)
            ? $resultsPerPage
            : NotificationService::NOTIFICATION_PER_PAGE_DEFAULT;

        $notifications      = $this->service->getPaginatedNotifications($page, $notificationsPerPage);
        $totalNotifications = $this->service->countUserNotifications();

        $totalPages = intval(ceil($totalNotifications / $notificationsPerPage));

        return response()->json([
            'success'            => true,
            'notifications'      => $notifications,
            'totalNotifications' => $totalNotifications,
            'totalPages'         => $totalPages,
        ], 200);
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
