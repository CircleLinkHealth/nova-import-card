<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\PracticeSpecificLetter;

use App\Contracts\SelfEnrollmentLetter;
use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Http\Controllers\EnrollmentLetterDefaultConfigs;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;

class CalvaryMedicalClinicLetter extends EnrollmentLetterDefaultConfigs implements SelfEnrollmentLetter
{
    /**
     * @var array
     */
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
     * @var
     */
    protected $isSurveyOnlyUser;
    /**
     * @var
     */
    protected $letterPages;
    /**
     * @var Practice
     */
    protected $practice;
    /**
     * @var
     */
    protected $provider;
    /**
     * @var User
     */
    protected $userEnrollee;

    /**
     * CalvaryMedicalClinicLetter constructor.
     */
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
        return  $this->letterBladeView();
    }

    public static function signatures(Model $practiceLetter, Practice $practice, User $provider): string
    {
        return "<img src='$practiceLetter->customer_signature_src'  alt='$practice->dipslay_name' style='max-width: 100%;'/>";
    }
}
