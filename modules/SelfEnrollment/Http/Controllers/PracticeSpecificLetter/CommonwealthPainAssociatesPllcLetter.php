<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Http\Controllers\PracticeSpecificLetter;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\Contracts\SelfEnrollmentLetter;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\EnrollmentLetterDefaultConfigs;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController;
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

        return view("selfEnrollment::enrollment-letters.$className", [
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

    public static function signatures(Model $practiceLetter, Practice $practice, \CircleLinkHealth\Customer\Entities\User $provider): string
    {
        $signature = asset($practiceLetter->customer_signature_src);
        return "<img src=$signature  alt='$practice->dipslay_name' style='max-width: 100%;'/>";
    }
}
