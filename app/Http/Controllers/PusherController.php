<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\PusherNotificationService;

class PusherController extends Controller
{
    public $service;

    public function __construct(PusherNotificationService $pusherNotificationService)
    {
        $this->service = $pusherNotificationService;
    }

    /**
     * @param $receiverId
     * @param $attachmentId
     */
    public function markNotificationAsRead($receiverId, $attachmentId)
    {
        $this->service->markNotificationAsRead($receiverId, $attachmentId);
    }
}
