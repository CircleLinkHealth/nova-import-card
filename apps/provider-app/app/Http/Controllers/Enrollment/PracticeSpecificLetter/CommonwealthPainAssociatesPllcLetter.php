<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\PracticeSpecificLetter;

use App\Contracts\SelfEnrollmentLetter;
use App\Http\Controllers\EnrollmentLetterDefaultConfigs;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController;
use Illuminate\Database\Eloquent\Model;

class CommonwealthPainAssociatesPllcLetter extends EnrollmentLetterDefaultConfigs implements SelfEnrollmentLetter
{
    protected $baseLetter;
    /**
     * @var mixed
     */
    protected $enrollee;
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

    public function __construct(bool $hideButtons, array $baseLetter, Practice $practice, User $userEnrollee)
    {
        $this->constructorDefaultArguments($hideButtons, $baseLetter, $practice, $userEnrollee);
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
            'userEnrollee'           => $this->userEnrollee,
            'isSurveyOnlyUser'       => $this->isSurveyOnlyUser,
            'letterPages'            => $this->letterPages,
            'practiceDisplayName'    => $this->practice->display_name,
            'practiceLogoSrc'        => $this->baseLetter['letter']->practice_logo_src ?? SelfEnrollmentController::ENROLLMENT_LETTER_DEFAULT_LOGO,
            'signatoryNameForHeader' => $this->provider->display_name,
            'dateLetterSent'         => $baseLetterConfigs['dateLetterSent'],
            'hideButtons'            => $this->hideButtons,
            'buttonColor'            => $baseLetterConfigs['buttonColor'],
        ]);
    }

    public function letterSpecificView()
    {
        return $this->letterBladeView();
    }

    public static function signatures(Model $practiceLetter, Practice $practice, User $provider): string
    {
        return "<img src='$practiceLetter->customer_signature_src'  alt='$practice->dipslay_name' style='max-width: 100%;'/>";
    }
}
