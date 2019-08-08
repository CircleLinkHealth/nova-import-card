<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 8/6/19
 * Time: 8:14 PM
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