<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/03/2017
 * Time: 2:14 AM
 */

namespace App\Repositories\Cache;


use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserView
{
    private $viewHashKey;
    private $userIds;

    public function __construct(Collection $userIds, $viewHashKey = null)
    {
        $this->userIds = $userIds;
        $this->viewHashKey = $viewHashKey ?? 'view' . str_random('20');
    }

    /**
     * Store a fail response in the requesting User's view cache, basically notifying the User that this job failed.
     */
    public function storeFailResponse()
    {
        $this->userIds->map(function ($userId) {
            $title = 'There was an error when compiling the reports.';

            \Redis::rpush($this->getHashKeyForUser($userId), $this->userCachedNotificationFactory($title));
        });
    }

    /**
     * Get the hash key for the give User's cached views list
     *
     * @param $userId
     *
     * @return string
     */
    public function getHashKeyForUser($userId)
    {
        return "user:{$userId}:views";
    }

    /**
     * Create a User view
     *
     * @param $title
     * @param bool $view
     * @param array $data
     *
     * @return array
     */
    public function userCachedNotificationFactory($title, $description = '')
    {
        return json_encode([
            'key'         => $this->viewHashKey,
            'created_at'  => Carbon::now()->toDateTimeString(),
            'expires_at'  => Carbon::now()->addWeek()->toDateTimeString(),
            'title'       => $title,
            'description' => $description,
        ]);
    }

    /**
     * Store a fail response in the requesting User's view cache, basically notifying the User that this job failed.
     */
    public function storeViewInCache($view, $data)
    {
        \Cache::put($this->viewHashKey, [
            'view'       => $view,
            'created_at' => Carbon::now()->toDateTimeString(),
            'expires_at' => Carbon::now()->addWeek()->toDateTimeString(),
            'data'       => $data,
        ], 11000);
    }

    /**
     * Store a success response in the requesting User's view cache, basically notifying the User that this job was
     * completed successfully.
     */
    public function storeSuccessResponse()
    {
        $title = 'Nurse Invoices';

        $this->userIds->map(function ($userId) use ($title) {
            \Redis::rpush(
                $this->getHashKeyForUser($userId),
                $this->userCachedNotificationFactory($title)
            );
        });
    }
}