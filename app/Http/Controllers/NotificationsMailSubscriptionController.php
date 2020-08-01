<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\NotificationsForRole;
use App\Traits\NotificationSubscribable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationsMailSubscriptionController extends Controller
{
    use NotificationSubscribable;
    /**
     * @var NotificationsForRole
     */
    private $notificationsForRole;

    /**
     * NotificationsMailSubscriptionController constructor.
     */
    public function __construct(NotificationsForRole $notificationsForRole)
    {
        $this->notificationsForRole = $notificationsForRole;
    }

    /**
     * Creates an entry in unsubscribe_notification_mail.
     * The Request here is coming from outside CPM app. (email) that's why the route is signed.
     *
     * @return RedirectResponse|string
     */
    public function unsubscribe(Request $request)
    {
        if ( ! $request->hasValidSignature()) {
            abort(401);
        }
        $data                      = $request->input();
        $userId                    = auth()->id(); // route has 'auth' middleware
        $activityType              = $data['activityType'];
        $unsubscription            = $this->getOrCreateUnsubscription($userId, $activityType);
        $unsubscriptionJustCreated = $this->checkIfWasRecentlyCreated($unsubscription);

        if ( ! $unsubscriptionJustCreated && ! $unsubscription->trashed()) {
            abort(403, 'Unauthorized action.');
        }
        if ( ! $unsubscriptionJustCreated && $unsubscription->trashed()) {
            $this->updateSubscription($unsubscription);
        }

        return view('notifications.notification-mail-unsubscribe', compact('activityType'));
    }
}
