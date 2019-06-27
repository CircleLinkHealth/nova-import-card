<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Importer\StorageStrategies;

use App\AppConfig;
use CircleLinkHealth\Customer\Entities\User;

abstract class BaseStorageStrategy
{
    protected $blogId;
    protected $carePlanTemplateId;
    protected $user;

    public function __construct($blogId, User $user)
    {
        $this->blogId             = $blogId;
        $this->carePlanTemplateId = $user->carePlan->care_plan_template_id ?? AppConfig::pull('default_care_plan_template_id');
        $this->user               = $user;
    }
}
