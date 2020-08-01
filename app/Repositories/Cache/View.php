<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories\Cache;

use Carbon\Carbon;
use Illuminate\Support\Str;

class View
{
    private $viewHashKey;

    public function __construct($viewHashKey = null)
    {
        $this->viewHashKey = $viewHashKey ?? 'view'.Str::random('20');
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
        ], 660000);

        return $this->viewHashKey;
    }
}
