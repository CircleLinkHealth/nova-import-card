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
    public function __construct($mode = null)
    {
        $config = config('phaxio');

        if (!$mode) {
            $mode = app()->environment('production')
                ? 'production'
                : 'test';
        }


        $this->phaxio = new Phaxio($config[$mode]['key'], $config[$mode]['secret'], $config['host']);
    }

    public function getStatus($faxId)
    {
        return $this->phaxio->faxStatus($faxId);
    }

    public function send(
        $to,
        $files
    ) {
        return $this->phaxio->sendFax($to, $files);
    }
}