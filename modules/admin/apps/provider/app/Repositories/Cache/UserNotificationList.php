<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories\Cache;

use App\Constants;
use App\Contracts\UserNotificationListInterface;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;

class UserNotificationList implements UserNotificationListInterface
{
    private $userId;
    private $viewHashKey;

    public function __construct($user, $viewHashKey = null)
    {
        $this->userId = is_a($user, User::class)
            ? $user->id
            : $user;
        $this->viewHashKey = $viewHashKey;
    }

    /**
     * Get the User's cached views.
     *
     * @param int $start
     * @param int $end
     *
     * @return static
     */
    public function all($start = 0, $end = -1)
    {
        return collect(\RedisManager::lrange($this->userHashKey(), $start, $end))->map(function ($json) {
            $cache = json_decode($json, true);

            $now = Carbon::now();
            $expires = Carbon::parse($cache['expires_at']);

            if ($now->greaterThan($expires) || (isset($cache['key']) && ! \Cache::has($cache['key']))) {
                $this->delete($json);

                return false;
            }

            return $cache;
        })
            ->filter()
            ->reverse();
    }

    /**
     * Returns the count of the User's Notifications.
     *
     * @return mixed
     */
    public function count()
    {
        return \RedisManager::llen($this->userHashKey());
    }

    public function delete($notification)
    {
        return \RedisManager::lrem($this->userHashKey(), 0, $notification);
    }

    /**
     * Add a Notification to the User's Notification List.
     *
     * @param string $title
     * @param string $description
     * @param null   $link
     * @param string $linkTitle
     */
    public function push($title = '', $description = '', $link = null, $linkTitle = 'Link')
    {
        $pushed = \RedisManager::rpush(
            $this->userHashKey(),
            $this->userCachedNotificationFactory($title, $description, $link, $linkTitle)
        );
    }

    /**
     * Get the hash key for the give User's cached views list.
     *
     * @return string
     */
    public function userHashKey()
    {
        return str_replace('{$userId}', $this->userId, Constants::CACHED_USER_NOTIFICATIONS);
    }

    /**
     * Create a User view.
     *
     * @param $title
     * @param string $description
     * @param null   $link
     * @param $linkTitle
     * @param null $cacheKey
     *
     * @return array
     */
    protected function userCachedNotificationFactory($title, $description = '', $link = null, $linkTitle, $cacheKey = null)
    {
        return json_encode([
            'key'         => $cacheKey,
            'created_at'  => Carbon::now()->toDateTimeString(),
            'expires_at'  => Carbon::now()->addWeek()->toDateTimeString(),
            'title'       => $title,
            'description' => $description,
            'link'        => $link,
            'linkTitle'   => $linkTitle,
        ]);
    }
}
