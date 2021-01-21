<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Raygun;

use CircleLinkHealth\Raygun\PsrLogger\RaygunLogger;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Raygun4php\RaygunClient;

class LaravelLogger extends RaygunLogger
{
    use EventTrait;

    /**
     * Create a new laravel logger instance.
     *
     * @param \Bugsnag\Client $client
     *
     * @return void
     */
    public function __construct(RaygunClient $client, Dispatcher $dispatcher = null)
    {
        parent::__construct($client);

        $this->dispatcher = $dispatcher;
    }

    /**
     * Get the underlying Monolog instance.
     *
     * @return \Monolog\Logger
     */
    public function getMonolog()
    {
    }

    /**
     * Register a daily file log handler.
     *
     * @param string $path
     * @param int    $days
     * @param string $level
     *
     * @return void
     */
    public function useDailyFiles($path, $days = 0, $level = 'debug')
    {
    }

    /**
     * Register a file log handler.
     *
     * @param string $path
     * @param string $level
     *
     * @return void
     */
    public function useFiles($path, $level = 'debug')
    {
    }

    /**
     * Format the parameters for the logger.
     *
     * @param mixed $message
     *
     * @return string
     */
    protected function formatMessage($message)
    {
        if (is_array($message)) {
            return var_export($message, true);
        }

        if ($message instanceof Jsonable) {
            return $message->toJson();
        }

        if ($message instanceof Arrayable) {
            return var_export($message->toArray(), true);
        }

        return $message;
    }
}
