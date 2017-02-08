<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 06/02/2017
 * Time: 8:40 PM
 */

namespace App\Contracts;


interface Efax
{
    public function send(
        $faxNumber,
        $pathOrMessage
    );

    public function getStatus($faxId);
}