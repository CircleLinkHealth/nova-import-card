<?php

namespace App\CLH\CCD\Importer\Parsers\Facades;


use Illuminate\Support\Facades\Facade;

class UserMetaParserHelpers extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'userMetaParserHelpers';
    }
}