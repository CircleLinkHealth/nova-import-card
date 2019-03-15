<?php

namespace CircleLinkHealth\Raygun;

use CircleLinkHealth\Raygun\EventTrait;
use Raygun4php\RaygunClient;
use CircleLinkHealth\Raygun\PsrLogger\RaygunLogger;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class LaravelLogger extends RaygunLogger implements Log
{
    use EventTrait;
    
    /**
     * Create a new laravel logger instance.
     *
     * @param \Bugsnag\Client                              $client
     * @param \Illuminate\Contracts\Events\Dispatcher|null $dispatcher
     *
     * @return void
     */
    public function __construct(RaygunClient $client, Dispatcher $dispatcher = null)
    {
        parent::__construct($client);
        
        $this->dispatcher = $dispatcher;
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
        //
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
        //
    }
    
    /**
     * Get the underlying Monolog instance.
     *
     * @return \Monolog\Logger
     */
    public function getMonolog()
    {
        //
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
