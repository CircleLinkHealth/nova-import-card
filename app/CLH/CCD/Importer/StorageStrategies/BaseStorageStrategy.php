<?php

namespace App\CLH\CCD\Importer\StorageStrategies;


use App\CLH\Contracts\DataTemplate;
use App\User;

abstract class BaseStorageStrategy
{
    protected $blogId;
    protected $user;

    public function __construct($blogId, User $user)
    {
        $this->blogId = $blogId;
        $this->user = $user;
    }
}