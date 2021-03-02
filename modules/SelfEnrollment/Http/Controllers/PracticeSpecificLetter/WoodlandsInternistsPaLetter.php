<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Http\Controllers\PracticeSpecificLetter;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SelfEnrollment\Entities\User;
use CircleLinkHealth\SelfEnrollment\Contracts\SelfEnrollmentLetter;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\EnrollmentLetterDefaultConfigs;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\PracticeLetterHelper\LettersHelper;
use Illuminate\Database\Eloquent\Model;

class WoodlandsInternistsPaLetter extends EnrollmentLetterDefaultConfigs implements SelfEnrollmentLetter
{
    public bool $disableButtons;
    public $enrollee;
    public $extraAddressValues;
    public $extraAddressValuesExists;
    public $practice;
    public $userEnrollee;

    /**
     * WoodlandsInternistsPaLetter constructor.
     */
    public function __construct(bool $hideButtons, array $baseLetter, Practice $practice, User $userEnrollee, bool $disableButtons = false)
    {
        $this->constructorDefaultArguments($hideButtons, $baseLetter, $practice, $userEnrollee);
        //        Extra for this practice.
        $this->extraAddressValues;
        $this->extraAddressValuesExists;
        $this->disableButtons = $disableButtons;
    }

    public function getBaseViewConfigs(): array
    {
        return $this->viewConfigurations($this->practice, $this->enrollee);
    }

    public function letterBladeView()
    {
        $baseLetterConfigs                  = $this->getBaseViewConfigs();
        $className                          = $baseLetterConfigs['className'];
        $letterViewParams                   = LettersHelper::propsWithExtraAddress($this, $baseLetterConfigs);
        $letterViewParams['disableButtons'] = $this->disableButtons;

        return view("selfEnrollment::enrollment-letters.$className", $letterViewParams);
    }

    public function letterSpecificView()
    {
        $this->extraAddressValues[] = $this->getExtraAddressValues($this->userEnrollee);

        $this->extraAddressValuesExists = ! empty(collect($this->extraAddressValues)->filter()->all());

        return $this->letterBladeView();
    }

    public static function signatures(Model $practiceLetter, Practice $practice, \CircleLinkHealth\Customer\Entities\User $provider): string
    {
        $signature = asset($practiceLetter->customer_signature_src);
        return "<img src=$signature  alt='$practice->dipslay_name' style='width: 300px;'/>";
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
