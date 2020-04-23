<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Raygun\PsrLogger;

use Exception;
use Illuminate\Support\Facades\App;
use Psr\Log\LogLevel;
use Raygun4php\RaygunClient;
use Throwable;

class RaygunLogger extends AbstractLogger
{
    /**
     * The Raygun client instance.
     *
     * @var \Raygun4php\RaygunClient
     */
    protected $client;

    /**
     * The minimum level required to notify Raygun.
     *
     * @var string
     */
    protected $notifyLevel = LogLevel::DEBUG;

    /**
     * Create a new raygun logger instance.
     */
    public function __construct(RaygunClient $client)
    {
        $this->client = $client;
    }

    /**
     * Log a message to the logs.
     *
     * @param string $level
     * @param mixed  $message
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $title = 'Log '.$level;
        if (isset($context['title'])) {
            $title = $context['title'];
            unset($context['title']);
        }

        $exception = null;
        if (isset($context['exception']) && ($context['exception'] instanceof Exception || $context['exception'] instanceof Throwable)) {
            $exception = $context['exception'];
            unset($context['exception']);
        } elseif ($message instanceof Exception || $message instanceof Throwable) {
            $exception = $message;
        }

        // Below theshold, do not send a notification
        if ( ! $this->aboveLevel($level, $this->notifyLevel)) {
            return;
        }

        if (config('cpm-module-raygun.enable_crash_reporting')) {
            if (null !== $exception) {
                $this->client->SendException($exception, $this->getTagsFor($exception), array_merge($context, ['on' => $exception->getFile().':'.$exception->getLine()]));
            } else {
                $this->client->SendError(500, $title.$this->formatMessage($message), $context['file'] ?? __FILE__, $context['line'] ?? __LINE__, [$level]);
            }
        }
    }

    /**
     * Set the notifyLevel of the logger, as defined in Psr\Log\LogLevel.
     *
     * @param string $notifyLevel
     *
     * @return void
     */
    public function setNotifyLevel($notifyLevel)
    {
        if ( ! in_array($notifyLevel, $this->getLogLevelOrder())) {
            syslog(LOG_WARNING, 'Raygun Warning: Invalid notify level supplied to Raygun Logger');
        } else {
            $this->notifyLevel = $notifyLevel;
        }
    }

    /**
     * Checks whether the selected level is above another level.
     *
     * @param mixed $level
     * @param mixed $base
     */
    protected function aboveLevel($level, $base)
    {
        $levelOrder = $this->getLogLevelOrder();
        $baseIndex  = array_search($base, $levelOrder);
        $levelIndex = array_search($level, $levelOrder);

        return $levelIndex >= $baseIndex;
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

        return $message;
    }

    /**
     * Returns a list of log levels in order.
     */
    protected function getLogLevelOrder()
    {
        return [
            LogLevel::DEBUG,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY,
        ];
    }

    /**
     * Get the severity for the logger.
     *
     * @param string $level
     *
     * @return string
     */
    protected function getSeverity($level)
    {
        if ( ! $this->aboveLevel($level, 'notice')) {
            return 'info';
        }
        if ( ! $this->aboveLevel($level, 'warning')) {
            return 'warning';
        }

        return 'error';
    }

    /**
     * Gets the tags to store with the exception.
     *
     * @param Exception|Throwable $exception
     *
     * @return array
     */
    protected function getTagsFor($exception)
    {
        return [get_class($exception), $exception->getMessage(), 'env:'.App::environment()];
    }

    /**
     * Ensure the given string is less than 100 characters.
     *
     * @param string $str
     *
     * @return string
     */
    protected function limit($str)
    {
        if (strlen($str) <= 100) {
            return $str;
        }

        return rtrim(substr($str, 0, 97)).'...';
    }
}
