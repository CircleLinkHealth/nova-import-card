<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\PusherNotificationService;
use CircleLinkHealth\Core\Entities\DatabaseNotification;

class NotificationController extends Controller
{
    public $service;

    /**
     * NotificationController constructor.
     *
     * @param PusherNotificationService $pusherNotificationService
     */
    public function __construct(PusherNotificationService $pusherNotificationService)
    {
        $this->service = $pusherNotificationService;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $notifications = DatabaseNotification::whereNotifiableId(auth()->id())->orderByDesc('id')->take(5)->get();

        return response()->json($notifications);
    }

    /**
     * @param $receiverId
     * @param $attachmentId
     */
    public function markNotificationAsRead($receiverId, $attachmentId)
    {
        $this->service->markNotificationAsRead($receiverId, $attachmentId);
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $notification = DatabaseNotification::findOrFail($id);

        return response()->json($notification->toArray());
    }
}
