<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\PusherNotificationService;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\JsonResponse;

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
     * @param $patientId
     *
     * @return mixed|string
     */
    public static function getPatientName($patientId)
    {
        return User::find($patientId)->display_name;
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {//figure out how to keep the unread notifications count(UI) since we are loading only 5.
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
     * @return JsonResponse
     */
    public function show($id)
    {
        $notification = DatabaseNotification::findOrFail($id);

        return response()->json($notification->toArray());
    }
}
