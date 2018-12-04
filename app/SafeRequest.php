<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 30/10/2018
 * Time: 18:33
 */

namespace App;

use Illuminate\Http\Request;

/**
 * Class SafeRequest
 *
 * Provides methods to read sanitized input from a request.
 * Uses strip_tags().
 *
 * @package App
 */
class SafeRequest extends Request
{
    /**
     * Safely (remove html tags) retrieve an input item from the request.
     *
     * @param  string $key
     * @param  string|array|null $default
     *
     * @return string|array
     */
    public function inputSafe($key = null, $default = null)
    {
        $res = $this->input($key, $default);
        return $res
            ? htmlspecialchars($res)
            : $res;
    }

    /**
     * Get safely all of the input and files for the request.
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function allSafe($keys = null)
    {
        $all = $this->all($keys);
        if (empty($all)) {
            return $all;
        }

        $result = array();
        foreach ($all as $key => $value) {
            $result[$key] = htmlspecialchars($value);
        }
        return $result;
    }
}
