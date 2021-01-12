<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ConditionCodeLookup\Services;

use CircleLinkHealth\ConditionCodeLookup\ConditionCodeLookup;
use CircleLinkHealth\ConditionCodeLookup\Entities\ConditionCodeLookupCache;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class ConditionCodeLookupService implements ConditionCodeLookup
{
    const ICD10 = 'ICD-10';
    const ICD9  = 'ICD-9';

    /** @var Client */
    private $client;

    /**
     * ConditionCodeLookupService constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param $code
     *
     * @throws \Exception
     *
     * @return array
     */
    public function any($code)
    {
        $type  = null;
        $value = null;
        collect(['icd9', 'icd10'])
            ->each(function ($item, $index) use (&$type, &$value, $code) {
                $value = $this->{$item}($code);
                if ($value) {
                    $type = 'icd9' === $item
                        ? self::ICD9
                        : self::ICD10;

                    //exit the loop
                    return false;
                }

                return true;
            });

        return [
            'type' => $type,
            'name' => $value,
        ];
    }

    /**
     * @param $code
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function icd10($code)
    {
        //1. try from cache
        $result       = null;
        $cacheEnabled = config('condition-code-lookup.cache.enable', false);
        if ($cacheEnabled) {
            $result = $this->getFromCache(self::ICD10, $code);
        }
        if ( ! $result) {
            $apiUrl     = config('condition-code-lookup.api_urls.icd10')[0];
            $requestUrl = "$apiUrl?terms=$code&sf=code";
            $response   = $this->client->get($requestUrl);

            $contents = $response->getBody()->getContents();
            if (200 !== $response->getStatusCode()) {
                throw new \Exception($contents);
            }

            $json = json_decode($contents);

            $result = Arr::last(Arr::last(Arr::last($json)));
            if ($result) {
                //2.1 store in cache
                $this->storeInCache(self::ICD10, $contents, $result, $requestUrl, $contents);
            }
        }

        return $result;
    }

    /**
     * @param $code
     *
     * @throws \Exception
     *
     * @return string
     */
    public function icd9($code)
    {
        //1. try from cache
        $result       = null;
        $cacheEnabled = config('condition-code-lookup.cache.enable', false);
        if ($cacheEnabled) {
            $result = $this->getFromCache(self::ICD9, $code);
        }
        if ( ! $result) {
            //2. try from api
            $apiUrl     = config('condition-code-lookup.api_urls.icd9')[0];
            $requestUrl = "$apiUrl?terms=$code&cf";
            $response   = $this->client->get($requestUrl);
            $contents   = $response->getBody()->getContents();
            if (200 !== $response->getStatusCode()) {
                throw new \Exception($contents);
            }

            $json   = json_decode($contents);
            $result = Arr::last(Arr::last(Arr::last($json)));
            if ($result) {
                //2.1 store in cache
                $this->storeInCache(self::ICD9, $contents, $result, $requestUrl, $contents);
            }
        }

        //3. return result
        return $result;
    }

    public function snomed($code)
    {
        // TODO: Implement snomed() method.
    }

    private function getFromCache($type, $code)
    {
        return ConditionCodeLookupCache::where('type', '=', $type)
            ->where('code', '=', $code)
            ->pluck('name')
            ->first();
    }

    private function storeInCache($type, $code, $name, $requestUrl, $rawResponse)
    {
        ConditionCodeLookupCache::create([
            'type'         => $type,
            'code'         => $code,
            'name'         => $name,
            'request_url'  => $requestUrl,
            'response_raw' => $rawResponse,
        ]);
    }
}
