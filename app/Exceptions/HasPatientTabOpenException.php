<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/10/16
 * Time: 12:19 PM
 */

namespace App\Exceptions;


class HasPatientTabOpenException extends \Exception
{
    /**
     * @var int
     */
    protected $statusCode = 403;
}