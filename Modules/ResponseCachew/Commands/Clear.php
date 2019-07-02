<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\Commands;

use CircleLinkHealth\ResponseCache\Events\ClearedResponseCache;
use CircleLinkHealth\ResponseCache\Events\ClearingResponseCache;
use CircleLinkHealth\ResponseCache\Events\FlushedResponseCache;
use CircleLinkHealth\ResponseCache\Events\FlushingResponseCache;
use CircleLinkHealth\ResponseCache\InvalidationProfiles\FlushUserCacheOnAnyRelatedModelChange;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class Clear extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear user specific caches.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'user-cache:clear';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(FlushUserCacheOnAnyRelatedModelChange $service)
    {
        event(new FlushingResponseCache());
        event(new ClearingResponseCache());

        $userIds = $this->hasArgument('userIds') ? $this->argument('userIds') : [];

        $output = $service->flush($userIds);

        event(new FlushedResponseCache());
        event(new ClearedResponseCache());

        if ( ! empty($output['tags'])) {
            $this->info('Cleared following tags:');
        }

        if (array_key_exists('success', $output)) {
            $this->info(implode(PHP_EOL, $output['tags']));

            if ($output['success']) {
                $this->line('Caches cleared!');
            } else {
                $this->error('Caches NOT cleared');
            }

            return;
        }

        foreach ($output as $tag => $success) {
            if ($success) {
                $this->line($tag);
            } else {
                $this->warn($tag);
            }
        }
    }

    protected function getArguments()
    {
        return [
            [
                'userIds',
                InputArgument::IS_ARRAY|InputArgument::OPTIONAL,
                'Users to run the command for. Leave empty to send to all.',
            ],
        ];
    }
}
