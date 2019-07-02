<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\Test;

use CircleLinkHealth\ResponseCache\Exceptions\CouldNotUnserialize;
use CircleLinkHealth\ResponseCache\ResponseSerializer;
use Symfony\Component\HttpFoundation\Response;

class ResponseSerializerTest extends TestCase
{
    /** @var string */
    protected $content;
    /** @var \CircleLinkHealth\ResponseCache\ResponseSerializer */
    protected $responseSerializer;

    /** @var string */
    protected $statusCode;

    public function setUp()
    {
        parent::setUp();

        $this->responseSerializer = new ResponseSerializer();

        $this->content = '<html>This is a response</html>';

        $this->statusCode = 500;
    }

    /** @test */
    public function it_can_serialize_and_unserialize_a_response()
    {
        $testResponse = Response::create($this->content, $this->statusCode, ['testHeader' => 'testValue']);

        $serializedResponse = $this->responseSerializer->serialize($testResponse);

        $this->assertTrue(is_string($serializedResponse));

        $unserializedResponse = $this->responseSerializer->unserialize($serializedResponse);

        $this->assertInstanceOf(Response::class, $unserializedResponse);

        $this->assertEquals($this->content, $unserializedResponse->getContent());

        $this->assertEquals($this->statusCode, $unserializedResponse->getStatusCode());

        $this->assertEquals('testValue', $unserializedResponse->headers->get('testHeader'));
    }

    /** @test */
    public function it_throws_an_exception_when_something_else_than_a_response_is_unserialized()
    {
        $this->expectException(CouldNotUnserialize::class);

        $this->responseSerializer->unserialize('b:0;');
    }
}
