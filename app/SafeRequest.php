<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Http\Request;

/**
 * Class SafeRequest.
 *
 * Provides methods to read sanitized input from a request.
 * Uses strip_tags().
 */
class SafeRequest extends Request
{
    /**
     * Get safely all of the input and files for the request.
     *
     * @param array|mixed $keys
     *
     * @return array
     */
    public function allSafe($keys = null)
    {
        $all = $this->all($keys);
        if (empty($all)) {
            return $all;
        }

        $result = [];
        foreach ($all as $key => $value) {
            if (is_array($value) || 'patient-email-body' == $key) {
                $result[$key] = $value;
            } else {
                $result[$key] = htmlspecialchars($value, ENT_NOQUOTES);
            }
        }

        return $result;
    }

    /**
     * Safely (remove html tags) retrieve an input item from the request.
     *
     * @param string            $key
     * @param array|string|null $default
     *
     * @return array|string
     */
    public function inputSafe($key = null, $default = null)
    {
        $res = $this->input($key, $default);

        return $res
            ? htmlspecialchars($res, ENT_NOQUOTES)
            : $res;
    }
}
