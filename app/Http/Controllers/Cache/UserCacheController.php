<?php

namespace App\Http\Controllers\Cache;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserCacheController extends Controller
{
    public function getCachedViewByKey($key) {
        $cached = \Cache::get($key);

        if (empty($cached)) {
            return "This view has expired.";
        }

        if (!$cached['view']) {
            return $cached['message'];
        }

        return view($cached['view'], $cached['data']);
    }
}
