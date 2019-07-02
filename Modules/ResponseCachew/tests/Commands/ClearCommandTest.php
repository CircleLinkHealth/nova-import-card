<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\Test\Commands;

use CircleLinkHealth\ResponseCache\Events\ClearedResponseCache;
use CircleLinkHealth\ResponseCache\Events\ClearingResponseCache;
use CircleLinkHealth\ResponseCache\Events\FlushedResponseCache;
use CircleLinkHealth\ResponseCache\Events\FlushingResponseCache;
use CircleLinkHealth\ResponseCache\ResponseCacheRepository;
use CircleLinkHealth\ResponseCache\Test\TestCase;
use Event;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Artisan;

class ClearCommandTest extends TestCase
{
    /** @test */
    public function it_will_clear_all_when_tags_are_not_defined()
    {
        $responseCache = $this->createTaggableResponseCacheStore(null);
        $appCache      = $this->app['cache']->store('array');

        $appCache->forever('appData', 'someValue');
        $responseCache->clear();

        $this->assertNull($appCache->get('appData'));
    }

    /** @test */
    public function it_will_clear_the_cache()
    {
        $firstResponse = $this->call('GET', '/random');

        Artisan::call('responsecache:clear');

        $secondResponse = $this->call('GET', '/random');

        $this->assertRegularResponse($firstResponse);
        $this->assertRegularResponse($secondResponse);

        $this->assertDifferentResponse($firstResponse, $secondResponse);
    }

    /** @test */
    public function it_will_fire_events_when_clearing_the_cache()
    {
        Event::fake();

        Artisan::call('responsecache:clear');

        Event::assertDispatched(FlushingResponseCache::class);
        Event::assertDispatched(ClearingResponseCache::class);
        Event::assertDispatched(FlushedResponseCache::class);
        Event::assertDispatched(ClearedResponseCache::class);
    }

    protected function createTaggableResponseCacheStore($tag): Repository
    {
        $this->app['config']->set('responsecache.cache_store', 'array');
        $this->app['config']->set('responsecache.cache_tag', $tag);

        // Simulating construction of Repository inside of the service provider
        return $this->app->contextual[ResponseCacheRepository::class][$this->app->getAlias(Repository::class)]();
    }
}
