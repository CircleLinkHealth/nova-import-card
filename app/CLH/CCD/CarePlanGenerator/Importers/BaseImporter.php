<?php

namespace App\CLH\CCD\CarePlanGenerator\Importers;


use App\CLH\Contracts\DataTemplate;

abstract class BaseImporter
{
    protected $blogId;
    protected $userId;

    public function __construct($blogId, $userId)
    {
        $this->blogId = $blogId;
        $this->userId = $userId;
    }
}