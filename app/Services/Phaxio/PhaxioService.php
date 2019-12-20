<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Phaxio;

use App\Contracts\Efax;
use Phaxio;

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
     * @param $to
     * @param array|string $files
     *
     * @return mixed|Phaxio\Fax
     */
    public function send($to, $files)
    {
        return $this->fax->faxes()->create(['to' => $to, 'file' => $this->prepareFiles($files)]);
    }

    private function prepareFiles($files)
    {
        if ( ! $files) {
            return [];
        }

        if ( ! is_array($files)) {
            $files = [$files];
        }

        $handles = [];

        foreach ($files as $file) {
            if (file_exists($file)) {
                $handles[] = fopen($file, 'r');
            }
        }

        return $handles;
    }
}
