<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;

abstract class TakesEnrolleeCcdaImportTask extends BaseCcdaImportTask
{
    /** @var Enrollee */
    protected $enrollee;

    public static function for(User $patient, Ccda $ccda, Enrollee $enrollee = null)
    {
        $static = new static($patient, $ccda);
        if ($enrollee instanceof Enrollee) {
            $static->setEnrollee($enrollee);
        }

        return $static->import();
    }

    /**
     * @param mixed $enrollee
     */
    public function setEnrollee(Enrollee $enrollee): void
    {
        $this->enrollee = $enrollee;
    }
}
