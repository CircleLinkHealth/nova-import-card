<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\LargePayloadSqsQueue\Queue\Connectors;

use Aws\Sqs\SqsClient;
use CircleLinkHealth\LargePayloadSqsQueue\Queue\LargePayloadSqsQueue;
use CircleLinkHealth\LargePayloadSqsQueue\Support\Arr;
use Illuminate\Queue\Connectors\SqsConnector;

class Connector extends SqsConnector
{
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        if ( ! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        $group        = Arr::pull($config, 'group', 'default');

        return new LargePayloadSqsQueue(
            new SqsClient($config),
            $config['queue'],
            Arr::get($config, 'prefix', ''),
            Arr::get($config, 'suffix', ''),
            $group
        );
    }

    /**
     * Get the default configuration for SQS.
     *
     *
     * @return array
     */
    protected function getDefaultConfiguration(array $config)
    {
        // Laravel >= 5.1 has the "getDefaultConfiguration" method.
        if (method_exists(get_parent_class(), 'getDefaultConfiguration')) {
            return parent::getDefaultConfiguration($config);
        }

        return array_merge([
            'version' => 'latest',
            'http'    => [
                'timeout'         => 60,
                'connect_timeout' => 60,
            ],
        ], $config);
    }
}
