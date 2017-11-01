<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/01/2017
 * Time: 9:30 PM
 */

namespace App\Traits\Cache;


use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

trait UserRedisCache
{
    /**
     * Get the User's cached views
     *
     * Example Cached View:
     *
     * [
     * 'key'        => $key,
     * 'created_at' => Carbon::now()->toDateTimeString(),
     * 'expires_at' => Carbon::now()->addWeek()->toDateTimeString(),
     * 'view'       => 'billing.nurse.list',
     * 'message'    => 'The Nurse Invoices you requested are ready!',
     * 'data'       => [
     * 'invoices' => $links,
     * 'data'     => $data,
     * 'month'    => $month,
     * ],
     * ]
     *
     * @param int $start
     * @param int $end
     *
     * @return static
     */
    public function cachedViews($start = 0, $end = -1)
    {
        return collect(Redis::lrange("user{$this->id}views", $start, $end))->map(function ($json) {
            $array = json_decode($json, true);

            $now = Carbon::now();
            $expires = Carbon::parse($array['expires_at']);

            if ($now->greaterThan($expires)) {
                Redis::lrem("user{$this->id}views", 0, $json);

                return false;
            }

            return $array;
        })->filter();
    }

    /**
     * Returns the cached view count
     *
     * @return mixed
     */
    public function cachedViewCount()
    {
        return Redis::llen("user{$this->id}views");
    }
}