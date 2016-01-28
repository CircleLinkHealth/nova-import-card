<?php

namespace App\CLH\Contracts\CCD;


use App\CLH\Contracts\DataTemplate;

interface Parser
{
    public function parse();

    public function save($data);
}