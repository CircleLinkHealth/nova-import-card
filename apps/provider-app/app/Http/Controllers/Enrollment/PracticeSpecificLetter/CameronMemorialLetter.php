<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\PracticeSpecificLetter;

use App\Contracts\SelfEnrollmentLetter;
use App\Http\Controllers\Enrollment\PracticeLetterHelper\LettersHelper;
use App\Http\Controllers\EnrollmentLetterDefaultConfigs;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Database\Seeders\GenerateCameronLetter;
use Illuminate\Database\Eloquent\Model;

class CameronMemorialLetter extends EnrollmentLetterDefaultConfigs implements SelfEnrollmentLetter
{
    public bool $disableButtons;
    public $enrollee;
    public $extraAddressValues;
    public $extraAddressValuesExists;
    public $practice;
    public $userEnrollee;

    public function __construct(bool $hideButtons, array $baseLetter, Practice $practice, User $userEnrollee, bool $disableButtons = false)
    {
        $this->constructorDefaultArguments($hideButtons, $baseLetter, $practice, $userEnrollee);
        $this->extraAddressValues;
        $this->extraAddressValuesExists;
        $this->disableButtons = $disableButtons;
    }

    public function getBaseViewConfigs(): array
    {
        return $this->viewConfigurations($this->practice, $this->enrollee);
    }

    public static function groupSharedSignatoryName($uiRequests, User $userProvider)
    {
        if ( ! empty($uiRequests)) {
            $millersTeam = LettersHelper::getUiRequestDataFor($uiRequests, GenerateCameronLetter::MILLER_SIGNATURE);
            $faursTeam   = LettersHelper::getUiRequestDataFor($uiRequests, GenerateCameronLetter::FAUR_SIGNATURE);

            if (in_array($userProvider->id, $millersTeam)) {
                return $millersTeam['signatory_group_name'];
            }

            if (in_array($userProvider->id, $faursTeam)) {
                return $faursTeam['signatory_group_name'];
            }
        }
        // Extreme case. Lets show something instead of nothing.
        return $userProvider->display_name.' '.$userProvider->suffix;
    }

    public function letterBladeView()
    {
        $baseLetterConfigs                  = $this->getBaseViewConfigs();
        $className                          = $baseLetterConfigs['className'];
        $letterViewParams                   = LettersHelper::propsWithExtraAddress($this, $baseLetterConfigs);
        $letterViewParams['disableButtons'] = $this->disableButtons;

        return view("enrollment-letters.$className", $letterViewParams);
    }

    public function letterSpecificView()
    {
        $this->extraAddressValues[] = $this->getExtraAddressValues($this->userEnrollee);

        $this->extraAddressValuesExists = ! empty(collect($this->extraAddressValues)->filter()->all());

        return $this->letterBladeView();
    }

    public static function signatures(Model $practiceLetter, Practice $practice, User $provider): string
    {
        $uiRequests = json_decode($practiceLetter->ui_requests);
        if ( ! empty($uiRequests)) {
            $millersTeam = LettersHelper::getUiRequestDataFor($uiRequests, GenerateCameronLetter::MILLER_SIGNATURE);
            $faursTeam   = LettersHelper::getUiRequestDataFor($uiRequests, GenerateCameronLetter::FAUR_SIGNATURE);

            if (in_array($provider->id, $millersTeam)) {
                return "'<img src='/img/signatures/cameron-memorial/millers_signature.png' alt='$practice->dipslay_name' style='max-width: 17%;'/>";
            }

            if (in_array($provider->id, $faursTeam)) {
                return "'<img src='/img/signatures/cameron-memorial/faurs_signature.png'  alt='$practice->dipslay_name' style='max-width: 14%;'/>";
            }
        }

        return $practice->display_name;
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
