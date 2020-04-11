<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Ccda;

abstract class BaseCcdaImportHook
{
    /**
     * @var User
     */
    protected $patient;
    /**
     * @var Ccda
     */
    protected $ccda;
    
    public function __construct(User $patient, Ccda $ccda)
    {
        $this->patient = $patient;
        $this->ccda = $ccda;
    }
}
