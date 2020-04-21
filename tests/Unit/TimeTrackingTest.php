<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use Tests\CustomerTestCase;

class TimeTrackingTest extends CustomerTestCase
{
    public function test_it_stores_ccm_time()
    {
        $this->singleActivityTest(
            [
                'patientId'  => $this->patient()->id,
                'providerId' => $this->careCoach()->id,
                'programId'  => $this->practice()->id,
                'activities' => [
                    [
                        'duration'   => 300, //in seconds
                        'start_time' => now()->toDateTimeString(),
                        'url'        => 'https://example.com/hello',
                        'url_short'  => 'https://example.com',
                        'name'       => 'example',
                        'title'      => 'title',
                    ],
                ],
            ]
        );
    }

    public function test_it_stores_non_ccm_time()
    {
        $this->singleActivityTest(
            [
                'patientId'  => '0',
                'providerId' => $this->careCoach()->id,
                'programId'  => $this->practice()->id,
                'activities' => [
                    [
                        'duration'   => 300, //in seconds
                        'start_time' => now()->toDateTimeString(),
                        'url'        => 'https://example.com/hello',
                        'url_short'  => 'https://example.com',
                        'name'       => 'example',
                        'title'      => 'title',
                    ],
                ],
            ]
        );
    }

    public function test_it_stores_time_if_program_id_is_empty_string()
    {
        $this->singleActivityTest(
            [
                'patientId'  => '0',
                'providerId' => $this->careCoach()->id,
                'programId'  => '',
                'activities' => [
                    [
                        'duration'   => 300, //in seconds
                        'start_time' => now()->toDateTimeString(),
                        'url'        => 'https://example.com/hello',
                        'url_short'  => 'https://example.com',
                        'name'       => 'example',
                        'title'      => 'title',
                    ],
                ],
            ]
        );
    }

    private function singleActivityTest(array $args)
    {
        $response = $this->call(
            'post',
            route('api.pagetracking'),
            $args
        );

        $response->assertStatus(201);

        //if we pass an empty string as program ID, the app will store a 0in the DB
        $programId = '' === $args['programId'] ? null : $args['programId'];

        foreach ($args['activities'] as $activity) {
            $this->assertDatabaseHas(
                'lv_page_timer',
                [
                    'patient_id'        => $args['patientId'],
                    'provider_id'       => $args['providerId'],
                    'program_id'        => $programId,
                    'duration'          => $activity['duration'],
                    'billable_duration' => $activity['duration'],
                ]
            );

            $actArgs = [
                'patient_id'  => $args['patientId'],
                'provider_id' => $args['providerId'],
                'duration'    => $activity['duration'],
            ];

            if ( ! empty($args['patientId'])) {
                $this->assertDatabaseHas(
                    'lv_activities',
                    $actArgs
                );
            } else {
                $this->assertDatabaseMissing(
                    'lv_activities',
                    $actArgs
                );
            }
        }
    }
}
