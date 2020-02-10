<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\NotificationsForRole;
use App\Traits\NotificationSubscribable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionsDashboardController extends Controller
{
    use NotificationSubscribable;

    /**
     * @return \Illuminate\Contracts\View\Factory|string|View
     */
    public function subscriptionsIndex()
    {
        $user = auth()->user();
        // Should never happen but just in case...It's temporary till we incorporate more roles to mailing list
        if ( ! $user->isCareCoach()) {
            return 'You are not authorized to view this page';
        }

        $role                 = $user->practiceOrGlobalRole();
        $subscriptionsForRole = new NotificationsForRole($role);

        $subscriptionsForMail = $subscriptionsForRole->subscriptionsForMail();

        $subscriptions = [];
        foreach ($subscriptionsForMail as $notificationClass => $emailSubscription) {
//            If 'checked' = user is subscribed. ELSE leave it an empty string. Im using 'checked' in blade's checkboxes
            $subscribeStatus = $this->checkSubscriptions($emailSubscription, $user->id) ? 'checked' : '';
            $subscriptions[] = [
                $subscribeStatus => $emailSubscription,
            ];
        }

        return view('notifications.notification-mail-subscriptions', compact('subscriptions'));
    }

    /**
     * Update subscriptions from "Subscriptions Dashboard".
     *
     * @throws \Exception
     *
     * @return RedirectResponse
     */
    public function updateSubscriptions(Request $request)
    {
        $checkedSubscriptionsFormForm = $request->input('subscriptionTypes');
        $user                         = auth()->user();
        $role                         = $user->practiceOrGlobalRole();
        $subscriptionsForRole         = new NotificationsForRole($role);
        $subscriptionsForMail         = $subscriptionsForRole->subscriptionsForMail();
        // if all checkboxes are unchecked (unsubscribed) in UI then $subscriptionsForMail = all that should be unsubscribed
        $unCheckedSubscriptionsFormForm = $subscriptionsForMail;
        $userId                         = $user->id;

        if ( ! empty($checkedSubscriptionsFormForm)) {
            foreach ($checkedSubscriptionsFormForm as $checkedActivityType) {
                $this->subscribeToNotification($checkedActivityType);
            }
            $unCheckedSubscriptionsFormForm = array_diff(array_values($subscriptionsForMail), $checkedSubscriptionsFormForm);
        }

        foreach ($unCheckedSubscriptionsFormForm as $unCheckedActivityType) {
            $uncheckedSubscription     = $this->getOrCreateUnsubscription($userId, $unCheckedActivityType);
            $unsubscriptionJustCreated = $this->checkIfWasRecentlyCreated($uncheckedSubscription);

            if ( ! $unsubscriptionJustCreated && $uncheckedSubscription->trashed()) {
                $this->updateSubscription($uncheckedSubscription);
            }
        }

        return redirect()->back();
    }
}
