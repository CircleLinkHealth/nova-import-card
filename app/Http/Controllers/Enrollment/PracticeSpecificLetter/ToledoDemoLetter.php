<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\PracticeSpecificLetter;

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\ProviderSignature;
use Carbon\Carbon;
use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ToledoDemoLetter
{
    private $baseLetter;
    /**
     * @var mixed
     */
    private $enrollee;
    /**
     * @var bool
     */
    private $hideButtons;
    /**
     * @var mixed
     */
    private $isSurveyOnlyUser;
    /**
     * @var mixed
     */
    private $letterPages;

    /**
     * @var mixed
     */
    private $provider;

    /**
     * ToledoDemoLetter constructor.
     */
    public function __construct(bool $hideButtons)
    {
        $this->baseLetter;
        $this->provider;
        $this->letterPages;
        $this->enrollee;
        $this->isSurveyOnlyUser;
        $this->hideButtons = $hideButtons;
    }

    public function letterBladeView($extraAddressValuesRequested, $logoStyleRequest, $extraAddressValues, Practice $practice)
    {
        $dateLetterSent = '???';
        $buttonColor    = SelfEnrollmentController::DEFAULT_BUTTON_COLOR;
        $className      = SelfEnrollmentController::getLetterClassName($practice->name);

        /** @var EnrollableInvitationLink $invitationLink */
        $invitationLink = $this->enrollee->getLastEnrollmentInvitationLink();
        if ($invitationLink) {
            $dateLetterSent = Carbon::parse($invitationLink->updated_at)->toDateString();
            $buttonColor    = $invitationLink->button_color;
        }

        return view("enrollment-letters.$className", [
            'userEnrollee'                => $this->enrollee,
            'isSurveyOnlyUser'            => $this->isSurveyOnlyUser,
            'letterPages'                 => $this->letterPages,
            'practiceDisplayName'         => $practice->display_name,
            'practiceLogoSrc'             => $this->baseLetter->practice_logo_src ?? SelfEnrollmentController::ENROLLMENT_LETTER_DEFAULT_LOGO,
            'signatoryNameForHeader'      => $this->provider->display_name,
            'dateLetterSent'              => $dateLetterSent,
            'hideButtons'                 => $this->hideButtons,
            'buttonColor'                 => $buttonColor,
            'logoStyleRequest'            => $logoStyleRequest,
            'extraAddressValues'          => $extraAddressValues,
            'extraAddressValuesRequested' => $extraAddressValuesRequested,
        ]);
    }

    public function letterSpecificView(array $baseLetter, Practice $practice, User $userEnrollee)
    {
        $this->setProperties($baseLetter);

        $uiRequests       = json_decode($this->baseLetter->ui_requests);
        $uiRequestsExists = ! is_null($uiRequests);
//        Toledo needs logo on the right.
        $logoStyleRequest = $uiRequestsExists ? $uiRequests->logo_position : '';
//        $logoStyleRequest = 'text-align:right';
//        Toledo needs some extra data in letter top left.
        $extraAddressHeader = $uiRequestsExists ? collect($uiRequests->extra_address_header) : collect();

        $extraAddressValues = collect()->first();
        if ( ! empty($extraAddressHeader)) {
            $models = $this->getModelsContainingNeededValues($extraAddressHeader);
            foreach ($models as $model => $props) {
//                @todo:use name.
                if ($practice->display_name === $model) {
                    $extraAddressValues[] = $this->getExtraAddressValues($props, $userEnrollee);
                }
//                Else use $model to query.
            }
        }

        return  $this->letterBladeView( ! empty(collect($extraAddressValues)->filter()->all()), $logoStyleRequest, $extraAddressValues, $practice);
    }

    public static function signatures(Model $practiceLetter, Practice $practice, User $provider)
    {
        $practiceSigSrc = '';
        if ( ! empty($practiceLetter->customer_signature_src)) {
            if (ProviderSignature::SIGNATURE_VALUE === $practiceLetter->customer_signature_src) {
                $practiceNameToGetSignature = $practice->name;
                if (isSelfEnrollmentTestModeEnabled()) {
//                    We need real practice's name and not toledo-demo. Signatures are saved: public/img/toledo-clinic/signatures
                    $practiceNameToGetSignature = 'toledo-clinic';
                }
                $npiNumber      = $provider->load('providerInfo')->providerInfo->npi_number;
                $type           = ProviderSignature::SIGNATURE_PIC_TYPE;
                $practiceSigSrc = "<img src='/img/signatures/$practiceNameToGetSignature/$npiNumber$type' alt='$practice->dipslay_name' style='max-width: 100%;'/>";
            }
        }

        return $practiceSigSrc;
    }

    /**
     * @return array
     */
    private function getExtraAddressValues(array $props, User $userEnrollee)
    {
        $practiceLocation      = $this->getPracticeLocation($userEnrollee);
        $practiceLocationArray = $practiceLocation->toArray();

        if (empty($practiceLocationArray)) {
            return [];
        }

        return collect($props[0])->mapWithKeys(function ($prop) use ($practiceLocationArray) {
            return  [
                $prop => $practiceLocationArray[$prop],
            ];
        })->toArray();
    }

    private function getModelsContainingNeededValues(\Illuminate\Support\Collection $extraAddressHeader)
    {
        return $extraAddressHeader->map(function ($model) {
            return [$model];
        });
    }

    /**
     * @return \App\Location|\Collection|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|object
     */
    private function getPracticeLocation(User $userEnrollee)
    {
        $enrolleePracticeLocationId = $userEnrollee->enrollee->location_id;

        $practiceLocation = collect();
        if ( ! empty($enrolleePracticeLocationId)) {
            $practiceLocation = Location::whereId($enrolleePracticeLocationId)->first();
        }

        // We want keep code execution if no practice location exists.
        if (is_null($practiceLocation)) {
            Log::info("Location for practice [$userEnrollee->id] not found. No practice location address will be displayed on letter");

            return collect();
        }

        return $practiceLocation;
    }

    /**
     * @param $baseLetter
     */
    private function setProperties(array $baseLetter)
    {
        $this->baseLetter       = $baseLetter['letter'];
        $this->provider         = $baseLetter['provider'];
        $this->letterPages      = $baseLetter['letterPages'];
        $this->enrollee         = $baseLetter['enrollee'];
        $this->isSurveyOnlyUser = $baseLetter['isSurveyOnlyUser'];
    }
}
