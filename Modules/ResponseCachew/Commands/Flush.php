<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\Commands;

use Illuminate\Console\Command;

class Flush extends Command
{
    protected $description = 'Flush the response cache (deprecated - use the clear method)';
    protected $signature   = 'responsecache:flush';

    public function handle()
    {
        $this->call('responsecache:clear');
    }
}
