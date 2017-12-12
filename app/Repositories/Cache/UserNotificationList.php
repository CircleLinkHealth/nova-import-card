<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/03/2017
 * Time: 2:14 AM
 */

namespace App\Repositories\Cache;


use App\Constants;
use App\User;
use Carbon\Carbon;

class UserNotificationList
{
    private $userId;
    private $viewHashKey;

    public function __construct($user, $viewHashKey = null)
    {
        $this->userId = is_a(User::class, $user)
            ? $user->id
            : $user;
        $this->viewHashKey = $viewHashKey;
    }

    /**
     * Add a Notification to the User's Notification List
     *
     * @param string $title
     * @param string $description
     * @param null $link
     * @param string $linkTitle
     */
    public function push($title = '', $description = '', $link = null, $linkTitle = 'Link')
    {
        \Redis::rpush(
            $this->userHashKey(),
            $this->userCachedNotificationFactory($title, $description, $link, $linkTitle)
        );
    }

    /**
     * Get the hash key for the give User's cached views list
     *
     *
     * @return string
     */
    public function userHashKey()
    {
        return str_replace('{$userId}', $this->userId, Constants::CACHED_USER_NOTIFICATIONS);
    }

    /**
     * Create a User view
     *
     * @param $title
     * @param string $description
     * @param null $link
     *
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
            'linkTitle'        => $linkTitle,
        ]);
    }

    /**
     * Returns the count of the User's Notifications
     *
     * @return mixed
     */
    public function count()
    {
        return \Redis::llen($this->userHashKey());
    }

    /**
     * Get the User's cached views
     *
     * @param int $start
     * @param int $end
     *
     * @return static
     */
    public function all($start = 0, $end = -1)
    {
        return collect(\Redis::lrange($this->userHashKey(), $start, $end))->map(function ($json) {
            $cache = json_decode($json, true);

            $now = Carbon::now();
            $expires = Carbon::parse($cache['expires_at']);

            if ($now->greaterThan($expires) || (isset($cache['key']) && !\Cache::has($cache['key']))) {
                $this->delete($json);

                return false;
            }

            return $cache;
        })
            ->filter()
            ->reverse();
    }

    public function delete($notification) {
        return \Redis::lrem($this->userHashKey(), 0, $notification);
    }
}