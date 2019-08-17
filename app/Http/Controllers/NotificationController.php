<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\PusherNotificationService;
use Carbon\Carbon;
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
    {
        $notifications               = DatabaseNotification::whereNotifiableId(auth()->id())->orderByDesc('id')->take(5)->get();
        $allUnreadNotificationsCount = DatabaseNotification::whereNotifiableId(auth()->id())->where('read_at', null)->count();

        $notificationsWithElapsedTime = $notifications->map(function ($notification) {
            $createdDateTime = Carbon::parse($notification->created_at);
            $notification['elapsed_time'] = $this->notificationElapsedTime($createdDateTime);

            return $notification;
        });

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
        $this->service->markNotificationAsRead($receiverId, $attachmentId);
    }

    /**
     * @param Carbon $createdDateTime
     *
     * @return string
     */
    public function notificationElapsedTime(Carbon $createdDateTime)
    {
        return $createdDateTime->diffForHumans(Carbon::parse(now()));
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        $notification    = DatabaseNotification::findOrFail($id);
        $createdDateTime = Carbon::parse($notification->created_at);
        //add elapsed_time to array
        $notification['elapsed_time'] = $this->notificationElapsedTime($createdDateTime);

        return response()->json($notification);
    }
}
