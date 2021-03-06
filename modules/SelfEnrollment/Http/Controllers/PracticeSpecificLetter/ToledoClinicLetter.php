<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Http\Controllers\PracticeSpecificLetter;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\Contracts\SelfEnrollmentLetter;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\EnrollmentLetterDefaultConfigs;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\PracticeLetterHelper\LettersHelper;
use CircleLinkHealth\SharedModels\ProviderSignature;
use Illuminate\Database\Eloquent\Model;

class ToledoClinicLetter extends EnrollmentLetterDefaultConfigs implements SelfEnrollmentLetter
{
    public $baseLetter;
    /**
     * @var mixed
     */
    public $enrollee;
    /**
     * @var mixed
     */
    public $extraAddressValues;
    /**
     * @var bool
     */
    public $extraAddressValuesExists;
    /**
     * @var bool
     */
    public $hideButtons;
    /**
     * @var mixed
     */
    public $isSurveyOnlyUser;
    /**
     * @var mixed
     */
    public $letterPages;
    /**
     * @var Practice
     */
    public $practice;

    /**
     * @var mixed
     */
    public $provider;
    /**
     * @var User
     */
    public $userEnrollee;

    /**
     * ToledoClinicLetter constructor.
     */
    public function __construct(bool $hideButtons, array $baseLetter, Practice $practice, User $userEnrollee)
    {
        $this->constructorDefaultArguments($hideButtons, $baseLetter, $practice, $userEnrollee);
//        Extra for this practice.
        $this->extraAddressValues;
        $this->extraAddressValuesExists;
    }

    public function getBaseViewConfigs(): array
    {
        return $this->viewConfigurations($this->practice, $this->enrollee);
    }

    public function letterBladeView()
    {
        $baseLetterConfigs = $this->getBaseViewConfigs();
        $className         = $baseLetterConfigs['className'];

        return view("selfEnrollment::enrollment-letters.$className", LettersHelper::propsWithExtraAddress($this, $baseLetterConfigs));
    }

    public function letterSpecificView()
    {
        $this->extraAddressValues[] = $this->getExtraAddressValues($this->userEnrollee);

        $this->extraAddressValuesExists = ! empty(collect($this->extraAddressValues)->filter()->all());

        return $this->letterBladeView();
    }

    public static function signatures(Model $practiceLetter, Practice $practice, User $provider): string
    {
        $practiceSigSrc = '';
        if ( ! empty($practiceLetter->customer_signature_src)) {
            if (ProviderSignature::SIGNATURE_VALUE === $practiceLetter->customer_signature_src) {
                $practiceNameToGetSignature = $practice->name;
                $npiNumber                  = $provider->load('providerInfo')->providerInfo->npi_number;
                $type                       = ProviderSignature::SIGNATURE_PIC_TYPE;
                $signature                  = asset("/img/signatures/$practiceNameToGetSignature/$npiNumber$type");
                $practiceSigSrc             = "<img src=$signature alt='$practice->dipslay_name' style='max-width: 100%;'/>";
            }
        }

        return $practiceSigSrc;
    }

    /**
     * @return array
     */
    private function getExtraAddressValues(User $userEnrollee)
    {
        $practiceLocation      = LettersHelper::getPracticeLocation($userEnrollee);
        $practiceLocationArray = $practiceLocation->toArray();

        if (empty($practiceLocationArray)) {
            return [];
        }

        $extraProps = [
            'address_line_1',
            'city',
            'state',
            'postal_code', // zip
        ];

        return LettersHelper::extraAddressValues($extraProps, $practiceLocationArray);
    }
}
