<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\DTO\CreatePageTimerParams;
use CircleLinkHealth\SharedModels\Services\PageTimerService;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserLoggedOut implements ShouldQueue, ShouldBeEncrypted
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param object $event
     */
    public function handle(Logout $event)
    {
        session()->put('last_login', null);

        /** @var User $user */
        $user = $event->user;
        if ($user) {
            //not really needed here, but is saves the trip to redis when trying to destroy a non-existing session
            $user->last_session_id = null;

            $user->is_online = false;
            $user->save();
            $this->createPageTimer($user);
        }
    }

    private function createPageTimer(User $user)
    {
        $params = (new CreatePageTimerParams())
            ->setActivity([
                'duration'              => 0,
                'chargeable_service_id' => null,
                'name'                  => 'logout',
                'title'                 => 'Logout',
                'url'                   => url()->current(),
                'url_short'             => '/auth/logout/',
                'start_time'            => now()->format('Y-m-d H:i:s'),
                'end_time'              => now()->format('Y-m-d H:i:s'),
            ])
            ->setPatientId(null)
            ->setProviderId($user->id)
            ->setProgramId($user->program_id);

        app(PageTimerService::class)->createPageTimer($params);
    }
}
