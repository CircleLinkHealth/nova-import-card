<?php

namespace App\CLH\Contracts\CCD;

interface StorageStrategy
{
    public function import($data);
}
