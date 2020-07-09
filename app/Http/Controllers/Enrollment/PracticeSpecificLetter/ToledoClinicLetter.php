<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\PracticeSpecificLetter;

use App\Contracts\SelfEnrollmentLetter;
use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Http\Controllers\EnrollmentLetterDefaultConfigs;
use App\ProviderSignature;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ToledoClinicLetter extends EnrollmentLetterDefaultConfigs implements SelfEnrollmentLetter
{
    protected $baseLetter;
    /**
     * @var mixed
     */
    protected $enrollee;
    /**
     * @var mixed
     */
    protected $extraAddressValues;
    /**
     * @var bool
     */
    protected $extraAddressValuesExists;
    /**
     * @var bool
     */
    protected $hideButtons;
    /**
     * @var mixed
     */
    protected $isSurveyOnlyUser;
    /**
     * @var mixed
     */
    protected $letterPages;
    /**
     * @var Practice
     */
    protected $practice;

    /**
     * @var mixed
     */
    protected $provider;
    /**
     * @var User
     */
    protected $userEnrollee;

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

        return view("enrollment-letters.$className", [
            'userEnrollee'             => $this->userEnrollee,
            'isSurveyOnlyUser'         => $this->isSurveyOnlyUser,
            'letterPages'              => $this->letterPages,
            'practiceDisplayName'      => $this->practice->display_name,
            'practiceLogoSrc'          => $this->baseLetter['letter']->practice_logo_src ?? SelfEnrollmentController::ENROLLMENT_LETTER_DEFAULT_LOGO,
            'signatoryNameForHeader'   => $this->provider->display_name,
            'dateLetterSent'           => $baseLetterConfigs['dateLetterSent'],
            'hideButtons'              => $this->hideButtons,
            'buttonColor'              => $baseLetterConfigs['buttonColor'],
            'extraAddressValues'       => $this->extraAddressValues,
            'extraAddressValuesExists' => $this->extraAddressValuesExists,
        ]);
    }

    public function letterSpecificView()
    {
        $this->extraAddressValues[] = $this->getExtraAddressValues($this->userEnrollee);

        $this->extraAddressValuesExists = ! empty(collect($this->extraAddressValues)->filter()->all());

        return  $this->letterBladeView();
    }

    public static function signatures(Model $practiceLetter, Practice $practice, User $provider): string
    {
        $practiceSigSrc = '';
        if ( ! empty($practiceLetter->customer_signature_src)) {
            if (ProviderSignature::SIGNATURE_VALUE === $practiceLetter->customer_signature_src) {
                $practiceNameToGetSignature = $practice->name;
                $npiNumber                  = $provider->load('providerInfo')->providerInfo->npi_number;
                $type                       = ProviderSignature::SIGNATURE_PIC_TYPE;
                $practiceSigSrc             = "<img src='/img/signatures/$practiceNameToGetSignature/$npiNumber$type' alt='$practice->dipslay_name' style='max-width: 100%;'/>";
            }
        }

        return $practiceSigSrc;
    }

    /**
     * @return array
     */
    private function getExtraAddressValues(User $userEnrollee)
    {
        $practiceLocation      = $this->getPracticeLocation($userEnrollee);
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

        return collect($extraProps)->mapWithKeys(function ($prop) use ($practiceLocationArray) {
            return  [
                $prop => $practiceLocationArray[$prop],
            ];
        })->toArray();
    }

    /**
     * @return \App\Location|\Collection|\Illuminate\Database\Eloquent\Builder|\Illuminate\Support\Collection|Model|object
     */
    private function getPracticeLocation(User $userEnrollee)
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
}
