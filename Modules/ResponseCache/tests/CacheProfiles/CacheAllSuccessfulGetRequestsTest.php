<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\Test\CacheProfiles;

use Carbon\Carbon;
use CircleLinkHealth\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequestsSpatie;
use CircleLinkHealth\ResponseCache\Test\TestCase;
use CircleLinkHealth\ResponseCache\Test\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheAllSuccessfulGetRequestsTest extends TestCase
{
    /** @var \CircleLinkHealth\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequestsSpatie */
    protected $cacheProfile;

    public function setUp()
    {
        parent::setUp();

        $this->cacheProfile = app(CacheAllSuccessfulGetRequestsSpatie::class);
    }

    /** @test */
    public function it_will_determine_that_a_successful_response_should_be_cached()
    {
        foreach (range(200, 399) as $statusCode) {
            $this->assertTrue($this->cacheProfile->shouldCacheResponse($this->createResponse($statusCode)));
        }
    }

    /** @test */
    public function it_will_determine_that_all_non_get_request_should_not_be_cached()
    {
        $this->assertFalse($this->cacheProfile->shouldCacheRequest($this->createRequest('post')));
        $this->assertFalse($this->cacheProfile->shouldCacheRequest($this->createRequest('patch')));
        $this->assertFalse($this->cacheProfile->shouldCacheRequest($this->createRequest('delete')));
    }

    /** @test */
    public function it_will_determine_that_an_error_should_not_be_cached()
    {
        foreach (range(400, 599) as $statusCode) {
            $this->assertFalse($this->cacheProfile->shouldCacheResponse($this->createResponse($statusCode)));
        }
    }

    /** @test */
    public function it_will_determine_that_get_requests_should_be_cached()
    {
        $this->assertTrue($this->cacheProfile->shouldCacheRequest($this->createRequest('get')));
    }

    /** @test */
    public function it_will_determine_to_cache_responses_for_a_certain_amount_of_time()
    {
        /** @var $expirationDate Carbon */
        $expirationDate = $this->cacheProfile->cacheRequestUntil($this->createRequest('get'));

        $this->assertTrue($expirationDate->isFuture());
    }

    /** @test */
    public function it_will_use_the_id_of_the_logged_in_user_to_differentiate_caches()
    {
        $this->assertEquals('', $this->cacheProfile->cacheNameSuffix($this->createRequest('get')));

        User::all()->map(function ($user) {
            auth()->login(User::find($user->id));
            $this->assertEquals($user->id, $this->cacheProfile->cacheNameSuffix($this->createRequest('get')));
        });
    }

    /**
     * Create a new request with the given method.
     *
     * @param $method
     *
     * @return \Illuminate\Http\Request
     */
    protected function createRequest($method)
    {
        $request = new Request();

        $request->setMethod($method);

        return $request;
    }

    /**
     * Create a new response with the given statusCode.
     *
     * @param int $statusCode
     *
     * @return \Symfony\Component\HttpFoundation\Response;
     */
    protected function createResponse($statusCode)
    {
        $response = new Response();

        $response->setStatusCode($statusCode);

        return $response;
    }
}
