<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Phaxio;

use App\Contracts\Efax;
use Phaxio\Phaxio;

class PhaxioService implements Efax
{
    /**
     * @var Phaxio
     */
    public $fax;

    /**
     * PhaxioService constructor.
     */
    public function __construct(Phaxio $phaxio)
    {
        $this->fax = $phaxio;
    }

    /**
     * @param $faxId
     *
     * @throws \Phaxio\PhaxioException
     *
     * @return array|mixed|\Phaxio\PhaxioOperationResult
     */
    public function getStatus($faxId)
    {
        return $this->fax->faxStatus($faxId);
    }

    /**
     * @param $to
     * @param $fileNames
     * @param array $options
     *
     * @throws \Phaxio\PhaxioException
     *
     * @return array|mixed|\Phaxio\PhaxioOperationResult
     */
    public function send($to, $fileNames, $options = [])
    {
        return $this->fax->sendFax($to, $fileNames, $options);
    }
}
