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
    /**
     * @var null
     */
    protected $payload;
    
    /**
     * BaseCcdaImportHook constructor.
     *
     * @param User $patient
     * @param Ccda $ccda
     * @param null $payload
     */
    public function __construct(User $patient, Ccda $ccda, $payload = null)
    {
        $this->patient = $patient;
        $this->ccda = $ccda;
        $this->payload = $payload;
    }
    
    /**
     * Run the hook
     *
     * @return mixed
     */
    abstract public function run();
}
