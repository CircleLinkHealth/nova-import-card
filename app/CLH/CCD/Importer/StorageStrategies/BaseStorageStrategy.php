<?php

namespace App\CLH\CCD\Importer\StorageStrategies;


use App\CLH\Contracts\DataTemplate;

abstract class BaseStorageStrategy
{
    protected $blogId;
    protected $userId;

    public function __construct($blogId, $userId)
    {
        $this->blogId = $blogId;
        $this->userId = $userId;
    }
}