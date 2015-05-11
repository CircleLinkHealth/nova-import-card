<?php namespace App;

use Chrisbjr\ApiGuard\Repositories\ApiKeyRepository;

class ApiKey extends ApiKeyRepository
{
    /**
     * Checks whether a key exists in the database or not
     *
     * @param $key
     * @return bool
     */
    public static function checkKeyExists($key)
    {
        $apiKeyCount = self::where('key', '=', $key)->limit(1)->count();

        if ($apiKeyCount > 0) return true;

        return false;
    }

}