<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache;

use Illuminate\Cache\Repository;
use Symfony\Component\HttpFoundation\Response;

class ResponseCacheRepository
{
    /** @var \Illuminate\Cache\Repository */
    protected $cache;

    /** @var \CircleLinkHealth\ResponseCache\ResponseSerializer */
    protected $responseSerializer;

    public function __construct(ResponseSerializer $responseSerializer, Repository $cache)
    {
        $this->cache              = $cache;
        $this->responseSerializer = $responseSerializer;
    }

    public function clear()
    {
        $this->cache->flush();
    }

    /**
     * @deprecated use the new clear method, this is just an alias
     */
    public function flush()
    {
        $this->clear();
    }

    public function forget(string $key): bool
    {
        return $this->cache->forget($key);
    }

    public function get(string $key): Response
    {
        return $this->responseSerializer->unserialize($this->cache->get($key));
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    /**
     * @param string                                     $key
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param \DateTime|int                              $minutes
     */
    public function put(string $key, $response, $minutes)
    {
        $this->cache->put($key, $this->responseSerializer->serialize($response), $minutes);
    }
}
