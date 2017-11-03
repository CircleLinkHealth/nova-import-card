<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/03/2017
 * Time: 6:20 PM
 */

namespace App\Repositories\Cache;


use Carbon\Carbon;

class View
{
    private $viewHashKey;

    public function __construct($viewHashKey = null)
    {
        $this->viewHashKey = $viewHashKey ?? 'view' . str_random('20');
    }

    /**
     * Store a view in the cache.
     *
     * @param $view
     * @param $data
     *
     * @return string
     */
    public function storeViewInCache($view, $data)
    {
        \Cache::put($this->viewHashKey, [
            'view'       => $view,
            'created_at' => Carbon::now()->toDateTimeString(),
            'expires_at' => Carbon::now()->addWeek()->toDateTimeString(),
            'data'       => $data,
        ], 11000);

        return $this->viewHashKey;
    }
}