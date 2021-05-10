<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\LargePayloadSqsQueue\Support;

use Illuminate\Support\Arr as BaseArr;
use function array_except;
use function array_get;
use function array_only;
use function array_pull;

class Arr
{
    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param array        $array
     * @param array|string $keys
     *
     * @return array
     */
    public static function except($array, $keys)
    {
        return class_exists(BaseArr::class) ? BaseArr::except($array, $keys) : array_except($array, $keys);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array|\ArrayAccess $array
     * @param int|string         $key
     * @param mixed              $default
     *
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        return class_exists(BaseArr::class) ? BaseArr::get($array, $key, $default) : array_get($array, $key, $default);
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param array        $array
     * @param array|string $keys
     *
     * @return array
     */
    public static function only($array, $keys)
    {
        return class_exists(BaseArr::class) ? BaseArr::only($array, $keys) : array_only($array, $keys);
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function pull(&$array, $key, $default = null)
    {
        return class_exists(BaseArr::class) ? BaseArr::pull($array, $key, $default) : array_pull($array, $key, $default);
    }
}
