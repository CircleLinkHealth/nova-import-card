<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Raygun;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Log\Events\MessageLogged;
use RuntimeException;

trait EventTrait
{
    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Get the event dispatcher instance.
     *
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Register a new callback handler for when a log event is triggered.
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public function listen(Closure $callback)
    {
        if ( ! isset($this->dispatcher)) {
            throw new RuntimeException('Events dispatcher has not been set.');
        }

        $this->dispatcher->listen(class_exists(MessageLogged::class) ? MessageLogged::class : 'illuminate.log', $callback);
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
        parent::log($level, $message, $context);

        $this->fireLogEvent($level, $message, $context);
    }

    /**
     * Set the event dispatcher instance.
     *
     * @return void
     */
    public function setEventDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Fires a log event.
     *
     * @param string $level
     * @param string $message
     *
     * @return void
     */
    protected function fireLogEvent($level, $message, array $context = [])
    {
        // If the event dispatcher is set, we will pass along the parameters to the
        // log listeners. These are useful for building profilers or other tools
        // that aggregate all of the log messages for a given "request" cycle.
        if ( ! isset($this->dispatcher)) {
            return;
        }

        if (class_exists(MessageLogged::class)) {
            $this->dispatcher->dispatch(new MessageLogged($level, $message, $context));
        } else {
            $this->dispatcher->fire('illuminate.log', compact('level', 'message', 'context'));
        }
    }
}
