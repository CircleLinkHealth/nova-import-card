<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\PracticeLetterHelper;

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class LettersHelper
{
    /**
     * @return array
     */
    public static function extraAddressValues(array $extraProps, array $practiceLocationArray)
    {
        return collect($extraProps)->mapWithKeys(function ($prop) use ($practiceLocationArray) {
            return  [
                $prop => $practiceLocationArray[$prop],
            ];
        })->toArray();
    }

    /**
     * @return \App\Location|\Collection|\Illuminate\Database\Eloquent\Builder|\Illuminate\Support\Collection|Model|object
     */
    public static function getPracticeLocation(User $userEnrollee)
    {
        $enrolleePracticeLocationId = $userEnrollee->enrollee->location_id;

        $practiceLocation = collect();
        if ( ! empty($enrolleePracticeLocationId)) {
            $practiceLocation = Location::whereId($enrolleePracticeLocationId)->first();
        }

        // We want continue code execution if no practice location exists.
        if (is_null($practiceLocation)) {
            Log::info("Location for practice [$userEnrollee->id] not found. No practice location address will be displayed on letter");

            return collect();
        }

        return $practiceLocation;
    }

    public static function propsWithExtraAddress($model, array $baseLetterConfigs)
    {
        return [
            'userEnrollee'             => $model->userEnrollee,
            'isSurveyOnlyUser'         => $model->isSurveyOnlyUser,
            'letterPages'              => $model->letterPages,
            'practiceDisplayName'      => $model->practice->display_name,
            'practiceLogoSrc'          => $model->baseLetter['letter']->practice_logo_src ?? SelfEnrollmentController::ENROLLMENT_LETTER_DEFAULT_LOGO,
            'signatoryNameForHeader'   => $model->provider->display_name,
            'dateLetterSent'           => $baseLetterConfigs['dateLetterSent'],
            'hideButtons'              => $model->hideButtons,
            'buttonColor'              => $baseLetterConfigs['buttonColor'],
            'extraAddressValues'       => $model->extraAddressValues,
            'extraAddressValuesExists' => $model->extraAddressValuesExists,
        ];
    }
}
