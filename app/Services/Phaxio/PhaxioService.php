<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/02/2017
 * Time: 7:25 PM
 */

namespace App\Services\Phaxio;

use App\Contracts\Efax;
use Phaxio\Phaxio;

class PhaxioService implements Efax
{
    public $fax;

    public function __construct(Phaxio $phaxio)
    {
        $this->fax = $phaxio;
    }

    public function getStatus($faxId)
    {
        return $this->fax->faxStatus($faxId);
    }

    public function send($to, $files)
    {
        return $this->fax->sendFax($to, $files);
    }
}
