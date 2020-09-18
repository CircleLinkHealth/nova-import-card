<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Contracts\CcdaImportTask;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\CcdToLogTranformer;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;

abstract class BaseCcdaImportTask implements CcdaImportTask
{
    /**
     * @var Ccda
     */
    protected $ccda;
    /**
     * @var Enrollee
     */
    protected $enrollee;

    /**
     * @var User
     */
    protected $patient;
    /**
     * @var CcdToLogTranformer
     */
    private $transformer;

    public function __construct(User $patient, Ccda $ccda, Enrollee $enrollee = null)
    {
        $this->patient  = $patient;
        $this->ccda     = $ccda;
        $this->enrollee = $enrollee;
    }

    public function chooseValidator($item)
    {
        foreach ($this->validators() as $className) {
            $validator = app($className);

            if ($validator->shouldValidate($item)) {
                return $validator;
            }
        }

        return false;
    }

    public static function for(User $patient, Ccda $ccda, Enrollee $enrollee = null)
    {
        return (new static($patient, $ccda, $enrollee))->import();
    }

    public function getTransformer()
    {
        if ( ! $this->transformer) {
            $this->transformer = app(CcdToLogTranformer::class);
        }

        return $this->transformer;
    }

    public function validate($item)
    {
        $validator = $this->chooseValidator($item);

        if ( ! $validator) {
            return false;
        }

        return $validator->isValid($item);
    }

    /**
     * @return \CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Validator[]
     */
    public function validators(): array
    {
        return \config('importer')['validators'];
    }

    protected function hasMisc(User $user, ?CpmMisc $misc)
    {
        return $user->cpmMiscs()->where('cpm_miscs.id', optional($misc)->id)->exists();
    }

    abstract protected function import();
}
