<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Helpers\FormRequests\Onboarding;

use Tests\Helpers\FormRequests\FormRequest;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 15/12/2016
 * Time: 2:05 PM.
 */
class LocationFormRequest extends FormRequest
{
    /**
     * Returns the attributes needed to delete this resource.
     *
     * @param mixed $count
     * @param mixed $params
     */
    public function delete(
        $count = 1,
        $params = []
    ): array {
        // TODO: Implement delete() method.
    }

    /**
     * Returns the attributes needed to create this resource.
     *
     * @param mixed $count
     * @param mixed $params
     */
    public function get(
        $count = 1,
        $params = []
    ): array {
        // TODO: Implement get() method.
    }

    /**
     * Returns the attributes needed to update this resource.
     *
     * @param mixed $count
     * @param mixed $params
     */
    public function patch(
        $count = 1,
        $params = []
    ): array {
        // TODO: Implement patch() method.
    }

    /**
     * Returns the attributes needed to store this resource.
     *
     * @param mixed $count
     * @param mixed $params
     */
    public function post(
        $count = 1,
        $params = []
    ): array {
        return [
            'locations' => [
                0 => [
                    'clinical_contact' => [
                        'firstName' => $this->faker->firstName,
                        'lastName'  => $this->faker->lastName,
                        'email'     => $this->faker->email,
                        'type'      => 'instead_of_billing_provider',
                    ],
                    'timezone'       => 'America/New_York',
                    'isComplete'     => true,
                    'errorCount'     => 0,
                    'validated'      => true,
                    'ehr_login'      => $this->faker->userName,
                    'ehr_password'   => $this->faker->text(20),
                    'name'           => $this->faker->company,
                    'address_line_1' => $this->faker->streetAddress,
                    'address_line_2' => $this->faker->numberBetween(1, 123),
                    'city'           => $this->faker->city,
                    'state'          => $this->faker->state,
                    'postal_code'    => $this->faker->postcode,
                    'phone'          => $this->faker->phoneNumber,
                ],
            ],
        ];
    }
}
