<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Helpers\FormRequests;

use Faker\Factory;

abstract class FormRequest
{
    /**
     * A Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * Returns the attributes needed to delete this resource.
     *
     * @param mixed $count
     * @param mixed $params
     */
    abstract public function delete(
        $count = 1,
        $params = []
    ): array;

    /**
     * Returns the attributes needed to create this resource.
     *
     * @param mixed $count
     * @param mixed $params
     */
    abstract public function get(
        $count = 1,
        $params = []
    ): array;

    /**
     * Returns the attributes needed to update this resource.
     *
     * @param mixed $count
     * @param mixed $params
     */
    abstract public function patch(
        $count = 1,
        $params = []
    ): array;

    /**
     * Returns the attributes needed to store this resource.
     *
     * @param mixed $count
     * @param mixed $params
     */
    abstract public function post(
        $count = 1,
        $params = []
    ): array;
}
