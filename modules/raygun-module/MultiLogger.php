<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Raygun;

use CircleLinkHealth\Raygun\PsrLogger\MultiLogger as BaseLogger;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Logging\Log;

class MultiLogger extends BaseLogger
{
    use EventTrait;

    /**
     * Create a new multi logger instance.
     *
     * @param \Psr\Log\LoggerInterface[] $loggers
     *
     * @return void
     */
    public function __construct(array $loggers, Dispatcher $dispatcher = null)
    {
        parent::__construct($loggers);

        $this->dispatcher = $dispatcher;
    }

    /**
     * Get the underlying Monolog instance.
     *
     * @return \Monolog\Logger
     */
    public function getMonolog()
    {
        foreach ($this->loggers as $logger) {
            if (is_callable([$logger, 'getMonolog'])) {
                $monolog = $logger->getMonolog();

                if (null === $monolog) {
                    continue;
                }

                return $monolog;
            }
        }
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
        foreach ($this->loggers as $logger) {
            if ($logger instanceof Log) {
                $logger->useDailyFiles($path, $days, $level);
            }
        }
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
        foreach ($this->loggers as $logger) {
            if ($logger instanceof Log) {
                $logger->useFiles($path, $level);
            }
        }
    }
}
