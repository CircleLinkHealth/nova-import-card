<?php

if ( ! function_exists('isProductionEnv')) {
    /**
     * Returns whether or not this is a Production server, ie. used by real users.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    function isProductionEnv()
    {
        return config('app.is_production_env');
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
        return config('app.is_queue_worker_env');
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