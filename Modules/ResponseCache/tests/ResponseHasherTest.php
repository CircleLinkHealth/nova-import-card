<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\Test;

use CircleLinkHealth\ResponseCache\CacheProfiles\CacheProfile;
use CircleLinkHealth\ResponseCache\Hasher\DefaultHasher;
use Illuminate\Http\Request;

class ResponseHasherTest extends TestCase
{
    protected $cacheProfile;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;
    /**
     * @var \CircleLinkHealth\ResponseCache\Hasher\DefaultHasher
     */
    protected $requestHasher;

    public function setUp()
    {
        parent::setUp();

        $this->cacheProfile = \Mockery::mock(CacheProfile::class);

        $this->request = Request::create('https://spatie.be');

        $this->requestHasher = new DefaultHasher($this->cacheProfile);
    }

    /** @test */
    public function it_can_generate_a_hash_for_a_request()
    {
        $this->cacheProfile->shouldReceive('cacheNameSuffix')->andReturn('cacheProfileSuffix');

        $this->assertEquals(
            'responsecache-1906a94776759c109dba2177825ade33',
            $this->requestHasher->getHashFor($this->request)
        );
    }
}
