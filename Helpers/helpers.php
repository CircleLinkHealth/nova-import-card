<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Core\Entities\AppConfig;

if ( ! function_exists('isProductionEnv')) {
    /**
     * Returns whether or not this is a Production server, ie. used by real users.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    function isProductionEnv()
    {
        return config('core.is_production_env');
    }
}

if ( ! function_exists('isQueueWorkerEnv')) {
    /**
     * Returns whether or not this server runs jobs from the queue.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    function isQueueWorkerEnv()
    {
        return config('core.is_queue_worker_env');
    }
}

if ( ! function_exists('isUnitTestingEnv')) {
    /**
     * Returns whether or not the test suite is running.
     *
     * @return bool|string
     */
    function isUnitTestingEnv()
    {
        return app()->environment(['testing']);
    }
}

if ( ! function_exists('upg0506IsEnabled')) {
    /**
     * Key: upg0506_is_enabled
     * Default: false.
     */
    function upg0506IsEnabled(): bool
    {
        $key = 'upg0506_is_enabled';
        $val = AppConfig::pull($key, null);
        if (null === $val) {
            return 'true' === AppConfig::set($key, false);
        }
        
        return 'true' === $val;
    }
}
