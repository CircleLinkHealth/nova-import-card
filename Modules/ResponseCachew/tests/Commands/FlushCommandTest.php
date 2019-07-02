<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\Test\Commands;

use CircleLinkHealth\ResponseCache\Test\TestCase;
use Illuminate\Support\Facades\Artisan;

class FlushCommandTest extends TestCase
{
    /** @test */
    public function it_points_to_the_updated_command()
    {
        $clearCommand = \Mockery::mock("\CircleLinkHealth\ResponseCache\Commands\Clear[handle]");
        $clearCommand->shouldReceive('handle')->once();
        $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand($clearCommand);

        Artisan::call('responsecache:flush');
    }
}
