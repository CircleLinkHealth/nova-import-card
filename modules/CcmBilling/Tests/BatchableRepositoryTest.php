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
