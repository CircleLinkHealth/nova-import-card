<?php


namespace CircleLinkHealth\SelfEnrollment\Http\Controllers\PracticeSpecificLetter;


use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SelfEnrollment\Entities\User;
use CircleLinkHealth\SelfEnrollment\Contracts\SelfEnrollmentLetter;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\EnrollmentLetterDefaultConfigs;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\PracticeLetterHelper\LettersHelper;
use Illuminate\Database\Eloquent\Model;

class DemoLetter extends EnrollmentLetterDefaultConfigs implements SelfEnrollmentLetter
{
    public bool $disableButtons;
    public $practice;
    public $enrollee;
    public array $extraAddressValues;
    /**
     * @var false
     */
    public bool $extraAddressValuesExists;

    /**
     * BethcareNewarkBethIsrael constructor.
     * @param bool $hideButtons
     * @param array $baseLetter
     * @param Practice $practice
     * @param User $userEnrollee
     * @param bool $disableButtons
     */
    public function __construct(bool $hideButtons, array $baseLetter, Practice $practice, User $userEnrollee, bool $disableButtons = false)
    {
        $this->constructorDefaultArguments($hideButtons, $baseLetter, $practice, $userEnrollee);
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
        $this->extraAddressValues = [];
        $this->extraAddressValuesExists = false;

        return $this->letterBladeView();
    }

    public static function signatures(Model $practiceLetter, Practice $practice, User $provider): string
    {
        $signature = asset($practiceLetter->customer_signature_src);
        return "<img src=$signature  alt='$practice->dipslay_name' style='width: 200px;'/>";
    }
}
