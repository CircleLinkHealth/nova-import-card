<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Exception;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BatchableStoreRepository
{
    const EXPIRATION_TIME_MINUTES = 60;
    const JSON_TYPE               = 'json';
    const MEDIA_TYPE              = 'media';

    public function get(string $batchId, string $dataType, array $chunkIds = []): Collection
    {
        $batchItemsCount = sizeof($chunkIds);

        $result = collect();
        if (empty($chunkIds)) {
            // no chunks, just get one entry from cache
            $chunkIds[] = '';
        }

        foreach ($chunkIds as $chunkId) {
            $item = $this->cache()->get($batchId.$dataType.$chunkId, null);
            if ($item) {
                $result->push($item);
            }
        }

        $jsonItemsCount = $result->count();
        Log::channel('database')->debug("BatchableStoreRepository::get | Batch[$batchId] | RawItemsCount[$batchItemsCount] | JsonItemsCount[$jsonItemsCount]");

        return $result;
    }

    /**
     * @param  mixed      $data
     * @param  mixed|null $chunkId
     * @throws \Exception
     */
    public function store(string $batchId, string $dataType, $data, $chunkId = '')
    {
        if ( ! in_array($dataType, [self::JSON_TYPE, self::MEDIA_TYPE])) {
            throw new Exception('not supported data type');
        }

        $entry = [
            'data_type' => $dataType,
            'data'      => $data,
        ];
        $key = $batchId.$dataType.$chunkId;
        $this->cache()->put($key, $entry, now()->addMinutes(self::EXPIRATION_TIME_MINUTES));

        Log::channel('database')->debug("BatchableStoreRepository::store | Key[$key]");
    }

    private function cache(): Repository
    {
        $store = isProductionEnv() ? 'dynamodb' : 'redis';

        return Cache::store($store);
    }
}
