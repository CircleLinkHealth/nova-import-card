<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use CircleLinkHealth\CcmBilling\Repositories\BatchableStoreRepository;
use CircleLinkHealth\Core\Tests\TestCase;
use CircleLinkHealth\Customer\Entities\Media;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;

class BatchableRepositoryTest extends TestCase
{
    use WithFaker;

    public function test_flat_map()
    {
        $data = [
            [
                'practice_id' => 1,
                'data_type'   => 'json',
                'data'        => [
                    [
                        'id'   => 1,
                        'name' => 'tasos',
                    ],
                    [
                        'id'   => 2,
                        'name' => 'kourpas',
                    ],
                ],
            ],
            [
                'practice_id' => 1,
                'data_type'   => 'json',
                'data'        => [
                    [
                        'id'   => 3,
                        'name' => 'george',
                    ],
                    [
                        'id'   => 4,
                        'name' => 'john',
                    ],
                ],
            ],
        ];

        $result = collect($data)
            ->map(fn ($item) => $item['data'])
            ->flatten(1)
            ->toArray();

        self::assertEquals([
            [
                'id'   => 1,
                'name' => 'tasos',
            ],
            [
                'id'   => 2,
                'name' => 'kourpas',
            ],
            [
                'id'   => 3,
                'name' => 'george',
            ],
            [
                'id'   => 4,
                'name' => 'john',
            ],
        ], $result);
    }

    public function test_it_reads_only_media_type_from_batch_in_cache()
    {
        $repo = app(BatchableStoreRepository::class);

        $batchId = (string) Str::uuid();
        $repo->store($batchId, 1, BatchableStoreRepository::JSON_TYPE, []);
        $repo->store($batchId, 1, BatchableStoreRepository::MEDIA_TYPE, 1);

        $batch = $repo->get($batchId, BatchableStoreRepository::MEDIA_TYPE);
        self::assertEquals(1, $batch[0]['data']);
    }

    public function test_it_stores_and_reads_data_from_batch_in_cache()
    {
        $batchId = (string) Str::uuid();
        $repo    = app(BatchableStoreRepository::class);
        $data    = collect($this->getFakeData());
        $data
            ->each(function ($practiceData) use ($repo, $batchId) {
                $practiceId = $practiceData['practice_id'];

                $media = Media::create([
                    'model_id'          => 1,
                    'model_type'        => 'test',
                    'collection_name'   => 'test',
                    'name'              => 'test',
                    'file_name'         => 'test',
                    'disk'              => 'local',
                    'size'              => 0,
                    'manipulations'     => '[]',
                    'custom_properties' => '[]',
                    'responsive_images' => '[]',
                ]);

                $repo->store($batchId, $practiceId, BatchableStoreRepository::MEDIA_TYPE, $media->id);
                $repo->store($batchId, $practiceId, BatchableStoreRepository::JSON_TYPE, $practiceData['patient_data']);
            });

        $practiceIds = collect();

        $batch = $repo->get($batchId);
        $batch
            ->groupBy('practice_id')
            ->each(function ($batchPerPractice, $practiceId) use ($practiceIds, $data) {
                $practiceIds->push($practiceId);
                $originalData = $data->firstWhere('practice_id', '=', $practiceId)['patient_data'];
                $cacheData = collect($batchPerPractice)->firstWhere('data_type', '=', BatchableStoreRepository::JSON_TYPE)['data'];
                self::assertEquals($originalData, $cacheData);
            })
            ->toArray();

        self::assertEquals($practiceIds->toArray(), $data->map(fn ($item) => $item['practice_id'])->toArray());
    }

    private function getFakeData(): array
    {
        return [
            [
                'practice_id'  => 1,
                'patient_data' => [
                    [
                        'prop1' => $this->faker->boolean,
                        'prop2' => $this->faker->randomNumber(),
                        'prop3' => $this->faker->sentence,
                    ],
                    [
                        'prop1' => $this->faker->boolean,
                        'prop2' => $this->faker->randomNumber(),
                        'prop3' => $this->faker->sentence,
                    ],
                ],
            ],
            [
                'practice_id'  => 2,
                'patient_data' => [
                    [
                        'prop1' => $this->faker->boolean,
                        'prop2' => $this->faker->randomNumber(),
                        'prop3' => $this->faker->sentence,
                    ],
                    [
                        'prop1' => $this->faker->boolean,
                        'prop2' => $this->faker->randomNumber(),
                        'prop3' => $this->faker->sentence,
                    ],
                ],
            ],
        ];
    }
}
