<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Services\AthenaAPI;

/*
   Copyright 2014 athenahealth, Inc.

   Licensed under the Apache License, Version 2.0 (the "License"); you
   may not use this file except in compliance with the License.  You
   may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
   implied.  See the License for the specific language governing
   permissions and limitations under the License.
*/

use CircleLinkHealth\Eligibility\Contracts\AthenaApiConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * This module contains utilities for communicating with the More Disruption Please API.
 */

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
class ConnectionV2 implements AthenaApiConnection
{
    const ATHENA_CACHE_KEY = 'athena_api_token';
    public  $practiceid;
    private $auth_url;
    private $baseurl;
    private $key;
    private $refresh_token;
    private $secret;
    private $token;
    
    private        $version;
    private string $authurl;
    
    /**
     * Connects to the host, authenticates to the specified API version using key and secret.
     *
     * @param string $version the specified API version to access
     * @param string $key the client key (also known as id)
     * @param string $secret the client secret
     * @param int|string $practiceid |null the practice id to be used in requests (optional)
     */
    public function __construct(string $version, string $key, string $secret, int $practiceid)
    {
        $this->version    = $version;
        $this->key        = $key;
        $this->secret     = $secret;
        $this->practiceid = $practiceid;
    }
    
    public function getAuthUrl()
    {
        $auth_prefixes = [
            'v1'           => 'https://api.platform.athenahealth.com/oauth2/v1/token',
            'preview1'     => 'https://api.preview.platform.athenahealth.com/oauth2/v1/token',
        ];
        
        return $auth_prefixes[$this->version];
    }
    
    public function getBaseUrl() {
        $base_prefixes = [
            'v1'           => "https://api.platform.athenahealth.com/v1/{$this->practiceid}",
            'preview1'     => "https://api.preview.platform.athenahealth.com/v1/{$this->practiceid}",
            'openpreview1' => 'ouathopenpreview/token',
        ];
    
        return $base_prefixes[$this->version];
    }
    
    /**
     * Perform at HTTP DELETE request and return an associative array of the API response.
     *
     * @param string $url the path (URI) of the resource
     * @param mixed array $parameters|null the request parameters
     * @param mixed array $headers|null    the request headers
     */
    public function DELETE($url, $parameters = null, $headers = null)
    {
        $new_url = $this->url_join($this->getBaseUrl(), $url);
        if ($parameters) {
            $new_url .= '?'.http_build_query($parameters);
        }
        
        $new_headers = [];
        if ($headers) {
            $new_headers = array_merge($new_headers, $headers);
        }
        
        return $this->authorized_call('DELETE', $new_url, [], $new_headers);
    }
    
    /**
     * Perform at HTTP GET request and return an associative array of the API response.
     *
     * @param string $url the path (URI) of the resource
     * @param mixed array $parameters|null the request parameters
     * @param mixed array $headers|null    the request headers
     */
    public function GET(
        $url,
        $parameters = null,
        $headers = null
    ) {
        $new_parameters = [];
        if ($parameters) {
            $new_parameters = array_merge($new_parameters, $parameters);
        }
        
        $new_headers = [];
        if ($headers) {
            $new_headers = array_merge($new_headers, $headers);
        }
        
        // Join up a URL and add the parameters, since GET requests require parameters in the URL.
        $new_url = $this->url_join($this->getBaseUrl(), $url);
        if ($new_parameters) {
            $new_url .= '?'.http_build_query($new_parameters);
        }
        
        return $this->authorized_call('GET', $new_url, [], $new_headers);
    }
    
    /**
     * Returns the current access_token.
     */
    public function get_token()
    {
        return $this->token;
    }
    
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * Perform at HTTP POST request and return an associative array of the API response.
     *
     * @param string $url the path (URI) of the resource
     * @param mixed array $parameters|null the request parameters
     * @param mixed array $headers|null    the request headers
     */
    public function POST($url, $parameters = null, $headers = null)
    {
        $new_parameters = [];
        if ($parameters) {
            $new_parameters = array_merge($new_parameters, $parameters);
        }
        
        // Make sure POSTs have the proper headers
        $new_headers = [
            'Content-type' => 'application/x-www-form-urlencoded',
        ];
        if ($headers) {
            $new_headers = array_merge($new_headers, $headers);
        }
        
        // Join up a URL
        $new_url = $this->url_join($this->getBaseUrl(), $url);
        
        return $this->authorized_call('POST', $new_url, $new_parameters, $new_headers);
    }
    
    /**
     * Perform at HTTP PUT request and return an associative array of the API response.
     *
     * @param string $url the path (URI) of the resource
     * @param mixed array $parameters|null the request parameters
     * @param mixed array $headers|null    the request headers
     */
    public function PUT($url, $parameters = null, $headers = null)
    {
        $new_parameters = [];
        if ($parameters) {
            $new_parameters = array_merge($new_parameters, $parameters);
        }
        
        // Make sure PUTs have the proper headers
        $new_headers = [
            'Content-type' => 'application/x-www-form-urlencoded',
        ];
        if ($headers) {
            $new_headers = array_merge($new_headers, $headers);
        }
        
        // Join up a URL
        $new_url = $this->url_join($this->getBaseUrl(), $url);
        
        return $this->authorized_call('PUT', $new_url, $new_parameters, $new_headers);
    }
    
    public function setPracticeId($practiceId)
    {
        $this->practiceid = $practiceId;
    }
    
    /**
     * This method abstracts away adding the authorization header to requests.
     *
     * @param mixed $verb
     * @param mixed $url
     * @param mixed $body
     * @param mixed $headers
     * @param mixed $secondcall
     */
    private function authorized_call(
        $verb,
        $url,
        $body,
        $headers,
        $secondcall = false
    ) {
        $token = $this->authenticate();
    
        $auth_header = ['Authorization' => 'Bearer '.$this->token];
        $response    = $this->call($verb, $url, $body, array_merge($auth_header, $headers));
    
        return $response;
    }
    
    /**
     * This method abstracts away the process of using stream contexts and file_get_contents for the
     * API calls.  It also does JSON decoding before return.
     *
     * @param mixed $verb
     * @param mixed $url
     * @param mixed $body
     * @param mixed $headers
     * @param mixed $secondcall
     */
    private function call($verb, $url, $body, $headers, $secondcall = false)
    {
        // It's easier to specify headers as an associative array, but making it a context requires
        // everything to be in the values of an indexed array.
        $formatted_headers = [];
        foreach ($headers as $k => $v) {
            $formatted_headers[] = $k.': '.$v;
        }
        
        // We shouldn't always be ignoring errors, but if we're calling this a second time, it's
        // because we found errors we want to ignore.  So we set ignore_errors to be the same as
        // $secondcall.
        $context = stream_context_create(
            [
                'http' => [
                    'method'        => $verb,
                    'header'        => $formatted_headers,
                    'content'       => http_build_query($body),
                    'ignore_errors' => $secondcall,
                ],
            ]
        );
        // NOTE: The warnings in file_get_contents are suppressed because the MDP API returns HTTP
        // status codes other than 200 (like 401 and 400) with information in the body that provides
        // a much better explanation than the code itself.
        $contents = @file_get_contents($url, false, $context);
        
        // $contents is false if there was an error, so if it was a 401 Not Authorized, propogate the
        // false.  Otherwise, try it again with ignored errors.
        if (false === $contents) {
            if ( ! app()->environment('local') && function_exists('http_parse_headers')) {
                $response_headers = http_parse_headers(implode("\r\n", $http_response_header));
                $response_code    = $response_headers['Response Code'];
                if (401 === $response_code) {
                    return false;
                }
            } else {
                // Hack to check for 401 response without needing to install PECL to be able to use http_parse_headers()
                if (isset($http_response_header, $http_response_header[0]) && Str::contains(
                        $http_response_header[0],
                        '401'
                    )) {
                    return false;
                }
            }
            
            if ( ! $secondcall) {
                return $this->call($verb, $url, $body, $headers, $secondcall = true);
            }
        }
        
        return json_decode($contents, true);
    }
    
    /**
     * This method joins together parts of a URL to make a valid one.
     *
     * Trims existing slashes from arguments and re-joins them with slashes.
     *
     * @param string $arg,... any number of strings to join
     */
    private function url_join()
    {
        return join(
            '/',
            array_map(
                function ($p) {
                    return trim($p, '/');
                },
                array_filter(
                    func_get_args(),
                    function ($value) {
                        return ! (is_null($value) || '' == $value);
                    }
                )
            )
        );
    }
    
    private function authenticate()
    {
        if ($this->cache()->has(self::ATHENA_CACHE_KEY)) {
            return $this->cache()->get(self::ATHENA_CACHE_KEY);
        }
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $this->getAuthUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&scope=athena/service/Athenanet.MDP.*");
        curl_setopt($ch, CURLOPT_USERPWD, "$this->key:$this->secret");
    
        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);
        
        $result = json_decode($result, true);
    
        $this->token         = $result['access_token'];
        $this->expires_in = $result['expires_in'];
        
        $this->cache()->put(self::ATHENA_CACHE_KEY, $this->token, $this->expires_in);
        
        return $this->token;
    }
    
    private function cache()
    {
        return Cache::driver(isProductionEnv() ? 'dynamodb' : config('cache.default'));
    }
}