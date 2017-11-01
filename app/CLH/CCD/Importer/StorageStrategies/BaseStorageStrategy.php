<?php

namespace App\CLH\CCD\Importer\StorageStrategies;

use App\CarePlanTemplate;
use App\CLH\Contracts\DataTemplate;
use App\Services\UserService;
use App\User;

abstract class BaseStorageStrategy
{
    protected $blogId;
    protected $carePlanTemplateId;
    protected $user;

    public function __construct($blogId, User $user)
    {
        $this->blogId = $blogId;
        $this->carePlanTemplateId = $user->service()->firstOrDefaultCarePlan($user)->getCarePlanTemplateIdAttribute();
        $this->user = $user;
    }
}
