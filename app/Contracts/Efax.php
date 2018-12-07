<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface Efax
{
    /**
     * @param $faxId
     *
     * @return mixed
     */
    public
    function
    getStatus($faxId);
    
    /**
     * @param $faxNumber
     * @param $pathOrMessage
     *
     * @return mixed
     */
    public
    
    function
    
    
    send(
        $faxNumber,
        $pathOrMessage
    );
}
