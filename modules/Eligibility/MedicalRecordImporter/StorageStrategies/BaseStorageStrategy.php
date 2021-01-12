<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies;

use CircleLinkHealth\Customer\Entities\User;

abstract class BaseStorageStrategy
{
    protected $blogId;
    protected $carePlanTemplateId;
    protected $user;

    public function __construct($blogId, User $user)
    {
        $this->blogId             = $blogId;
        $this->carePlanTemplateId = $user->service()->firstOrDefaultCarePlan($user)->care_plan_template_id;
        $this->user               = $user;
    }
}
