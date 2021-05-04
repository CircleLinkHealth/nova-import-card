<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BatchableStoreRepository
{
    const EXPIRATION_TIME_MINUTES = 60;
    const JSON_TYPE               = 'json';
    const MEDIA_TYPE              = 'media';

    public function get(string $batchId, string $dataType = null): Collection
    {
        $batch  = collect(Cache::get($batchId, []));
        $result = $batch
            ->when( ! is_null($dataType), fn ($coll) => $coll->where('data_type', '=', $dataType))
            ->values()
            ->map(function ($item) {
                if (BatchableStoreRepository::JSON_TYPE === $item['data_type']) {
                    $data = Cache::get($item['data']);

                    return [
                        'practice_id' => $item['practice_id'],
                        'data_type'   => BatchableStoreRepository::JSON_TYPE,
                        'data'        => $data,
                    ];
                }

                return $item;
            });

        $batchItemsCount = $batch->count();
        $jsonItemsCount  = $result->count();
        Log::channel('database')->debug("BatchableStoreRepository::get|Batch[$batchId]|RawItemsCount[$batchItemsCount]|JsonItemsCount[$jsonItemsCount]");

        return $result;
    }

    /**
     * @param  mixed      $data
     * @throws \Exception
     */
    public function store(string $batchId, int $practiceId, string $dataType, $data)
    {
        if ( ! in_array($dataType, [self::JSON_TYPE, self::MEDIA_TYPE])) {
            throw new Exception('not supported data type');
        }

        if (self::JSON_TYPE === $dataType) {
            $uuid = $batchId.'_'.((string) Str::uuid());
            Cache::put($uuid, $data, now()->addMinutes(self::EXPIRATION_TIME_MINUTES));
            $data = $uuid;
        }

        $batch = Cache::get($batchId, []);
        $entry = [
            'practice_id' => $practiceId,
            'data_type'   => $dataType,
            'data'        => $data,
        ];
        $batch[] = $entry;
        Cache::put($batchId, $batch, now()->addMinutes(self::EXPIRATION_TIME_MINUTES));

        $batchItemsCount = sizeof($batch);
        Log::channel('database')->debug("BatchableStoreRepository::store|Batch[$batchId]|Practice[$practiceId]|ItemsCount[$batchItemsCount]");
    }
}
