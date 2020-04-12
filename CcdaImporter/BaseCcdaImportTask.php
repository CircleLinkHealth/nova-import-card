<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Contracts\CcdaImportTask;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\CcdToLogTranformer;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;

abstract class BaseCcdaImportTask implements CcdaImportTask
{
    /**
     * @var CcdToLogTranformer
     */
    private $transformer;
    
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
    
    public static function for(User $patient, Ccda $ccda) {
        return (app(get_called_class(), ['patient' => $patient, 'ccda' => $ccda]))->import();
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
    
    public function getTransformer()
    {
        if (! $this->transformer) {
            $this->transformer = app(CcdToLogTranformer::class);
        }
        
        return $this->transformer;
    }
    
    protected function hasMisc(User $user, ?CpmMisc $misc)
    {
        return $user->cpmMiscs()->where('cpm_miscs.id', optional($misc)->id)->exists();
    }
    
    protected abstract function import();
}
