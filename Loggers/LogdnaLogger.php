<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Loggers;

use Zwijn\Monolog\Formatter\LogdnaFormatter;

class LogdnaLogger
{
    public function __invoke(array $config)
    {
        $logdnaHandler = new \Zwijn\Monolog\Handler\LogdnaHandler($config['ingestion_key'], $config['host_name'], $config['level']);
        $logdnaHandler->setFormatter(app(LogdnaFormatter::class));

        return $logdnaHandler;
    }
}
