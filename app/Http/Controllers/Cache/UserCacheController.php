<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;

class UserCacheController extends Controller
{
    public function getCachedViewByKey($key)
    {
        $cached = \Cache::get($key);

        if (empty($cached)) {
            return 'This view has expired.';
        }

        if ( ! $cached['view']) {
            return $cached['message'];
        }

        return view($cached['view'], $cached['data']);
    }
}
