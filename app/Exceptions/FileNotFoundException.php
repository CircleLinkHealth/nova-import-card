<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/28/2017
 * Time: 7:13 PM
 */

namespace App\Exceptions;


use Throwable;

class FileNotFoundException extends \Exception
{
    public function __construct($message = "File Not Found", $code = 404, Throwable $previous = null)
    {
    }
}