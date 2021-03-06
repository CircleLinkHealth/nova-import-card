<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\Vapor\Runtime;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

class Logger
{
    /**
     * The logger instance.
     *
     * @var \Monolog\Logger
     */
    protected static $logger;

    /**
     * Write error information to the log.
     *
     * @param  string $message
     * @return void
     */
    public static function error($message, array $context = [])
    {
        static::ensureLoggerIsAvailable();

        static::$logger->error($message, $context);
    }

    /**
     * Write general information to the log.
     *
     * @param  string $message
     * @return void
     */
    public static function info($message, array $context = [])
    {
        static::ensureLoggerIsAvailable();

        static::$logger->info($message, $context);
    }

    /**
     * Ensure the logger has been instantiated.
     *
     * @return void
     */
    protected static function ensureLoggerIsAvailable()
    {
        if (isset(static::$logger)) {
            return;
        }

        static::$logger = new MonologLogger('vapor', [
            (new StreamHandler('php://stderr'))->setFormatter(new JsonFormatter()),
        ]);
    }
}
