<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Contracts;

/**
 * This class abstracts away the HTTP connection and basic authentication from API calls.
 *
 * When an object of this class is constructed, it attempts to authenticate (using basic
 * authentication) at https://api.athenahealth.com/ using the key, secret, and version specified.
 * It stores the access token for later use.
 *
 * Whenever any of the HTTP request methods are called (GET, POST, etc.), the arguments are
 * converted into the proper form for the request.  The result is decoded from JSON and returned as
 * an array.
 *
 * The HTTP request methods take three parameters: a path (string), request parameters (array), and
 * headers (array).  These methods automatically prepend the API version and practiceid (if set) to
 * the URL.  Because not all API calls require parameters and custom headers are rare, both of those
 * arguments are optional.
 *
 * If an API response returns 401 Not Authorized, a new access token is obtained and the request is
 * retried.
 */
interface AthenaApiConnection
{
    /**
     * Perform at HTTP DELETE request and return an associative array of the API response.
     *
     * @param string      $url             the path (URI) of the resource
     * @param mixed array $parameters|null the request parameters
     * @param mixed array $headers|null    the request headers
     */
    public function DELETE($url, $parameters = null, $headers = null);

    /**
     * Perform at HTTP GET request and return an associative array of the API response.
     *
     * @param string      $url             the path (URI) of the resource
     * @param mixed array $parameters|null the request parameters
     * @param mixed array $headers|null    the request headers
     */
    public function GET($url, $parameters = null, $headers = null);

    /**
     * Returns the current access_token.
     */
    public function get_token();

    /**
     * Perform at HTTP POST request and return an associative array of the API response.
     *
     * @param string      $url             the path (URI) of the resource
     * @param mixed array $parameters|null the request parameters
     * @param mixed array $headers|null    the request headers
     */
    public function POST($url, $parameters = null, $headers = null);

    /**
     * Perform at HTTP PUT request and return an associative array of the API response.
     *
     * @param string      $url             the path (URI) of the resource
     * @param mixed array $parameters|null the request parameters
     * @param mixed array $headers|null    the request headers
     */
    public function PUT($url, $parameters = null, $headers = null);

    public function setPracticeId($practiceId);
}
