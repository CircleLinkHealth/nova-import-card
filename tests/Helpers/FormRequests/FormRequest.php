<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 15/12/2016
 * Time: 2:04 PM
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
     * @return array
     */
    abstract public function delete(
        $count = 1,
        $params = []
    ) : array;

    /**
     * Returns the attributes needed to create this resource.
     *
     * @return array
     */
    abstract public function get(
        $count = 1,
        $params = []
    ) : array;

    /**
     * Returns the attributes needed to update this resource.
     *
     * @return array
     */
    abstract public function patch(
        $count = 1,
        $params = []
    ) : array;

    /**
     * Returns the attributes needed to store this resource.
     *
     * @return array
     */
    abstract public function post(
        $count = 1,
        $params = []
    ) : array;
}