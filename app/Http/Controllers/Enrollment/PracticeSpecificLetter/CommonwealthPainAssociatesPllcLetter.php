<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\PracticeSpecificLetter;

use App\Contracts\SelfEnrollmentLetter;
use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use Carbon\Carbon;
use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;

class CommonwealthPainAssociatesPllcLetter implements SelfEnrollmentLetter
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
     * @var Practice
     */
    private $practice;

    /**
     * @var mixed
     */
    private $provider;
    /**
     * @var User
     */
    private $userEnrollee;

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
        $this->practice;
        $this->userEnrollee;
    }

    public function letterBladeView()
    {
        $dateLetterSent = '???';
        $buttonColor    = SelfEnrollmentController::DEFAULT_BUTTON_COLOR;
        $className      = SelfEnrollmentController::getLetterClassName($this->practice->name);

        /** @var EnrollableInvitationLink $invitationLink */
        $invitationLink = $this->enrollee->getLastEnrollmentInvitationLink();
        if ($invitationLink) {
            $dateLetterSent = Carbon::parse($invitationLink->updated_at)->toDateString();
            $buttonColor    = $invitationLink->button_color;
        }

        return view("enrollment-letters.$className", [
            'userEnrollee'           => $this->userEnrollee,
            'isSurveyOnlyUser'       => $this->isSurveyOnlyUser,
            'letterPages'            => $this->letterPages,
            'practiceDisplayName'    => $this->practice->display_name,
            'practiceLogoSrc'        => $this->baseLetter->practice_logo_src ?? SelfEnrollmentController::ENROLLMENT_LETTER_DEFAULT_LOGO,
            'signatoryNameForHeader' => $this->provider->display_name,
            'dateLetterSent'         => $dateLetterSent,
            'hideButtons'            => $this->hideButtons,
            'buttonColor'            => $buttonColor,
        ]);
    }

    public function letterSpecificView(array $baseLetter, Practice $practice, User $userEnrollee)
    {
        $this->setProperties($baseLetter, $practice, $userEnrollee);

        return  $this->letterBladeView();
    }

    public function setProperties(array $baseLetter, Practice $practice, User $userEnrollee)
    {
        $this->baseLetter       = $baseLetter['letter'];
        $this->provider         = $baseLetter['provider'];
        $this->letterPages      = $baseLetter['letterPages'];
        $this->enrollee         = $baseLetter['enrollee'];
        $this->isSurveyOnlyUser = $baseLetter['isSurveyOnlyUser'];
        $this->practice         = $practice;
        $this->userEnrollee     = $userEnrollee;
    }

    public static function signatures(Model $practiceLetter, Practice $practice, User $provider): string
    {
        return "<img src='$practiceLetter->customer_signature_src'  alt='$practice->dipslay_name' style='max-width: 100%;'/>";
    }
}
