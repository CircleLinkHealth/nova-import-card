<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    // Determine if the response cache middleware should be enabled.
    'enabled' => env('RESPONSE_CACHE_ENABLED', true),

    /*
     *  The given class will determinate if a request should be cached. The
     *  default class will cache all successful GET-requests.
     *
     *  You can provide your own class given that it implements the
     *  CacheProfile interface.
     */
    'cache_profile' => \App\Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests::class,

    /*
     * When using the default CacheRequestFilter this setting controls the
     * default number of minutes responses must be cached.
     */
    'cache_lifetime_in_minutes' => env('RESPONSE_CACHE_LIFETIME', 45),

    /*
     * This setting determines if a http header named "Laravel-responsecache"
     * with the cache time should be added to a cached response. This
     * can be handy when debugging.
     */
    'add_cache_time_header' => env('RESPONSE_CACHE_ENABLED', true),

    /*
     * Here you may define the cache store that should be used to store
     * requests. This can be the name of any store that is
     * configured in app/config/cache.php
     *
     * @CPM We rely on tags to create per user cache, so redis is required.
     */
    'cache_store' => env('RESPONSE_CACHE_DRIVER', 'redis'),

    /*
     * If the cache driver you configured supports tags, you may specify a tag name
     * here. All responses will be tagged. When clearing the responsecache only
     * items with that tag will be flushed.
     *
     * You may use a string or an array here.
     *
     * @CPM We rely on tags to create per user cache, so redis is required.
     */
    'cache_tag' => env('RESPONSE_CACHE_TAG', 'responsecachable'),
];
