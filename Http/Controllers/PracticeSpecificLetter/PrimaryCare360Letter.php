<?php


namespace CircleLinkHealth\SelfEnrollment\Http\Controllers\PracticeSpecificLetter;


use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SelfEnrollment\Contracts\SelfEnrollmentLetter;
use CircleLinkHealth\SelfEnrollment\Database\Seeders\GeneratePrimaryCare360;
use CircleLinkHealth\SelfEnrollment\Entities\User;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\EnrollmentLetterDefaultConfigs;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\PracticeLetterHelper\LettersHelper;
use Illuminate\Database\Eloquent\Model;

class PrimaryCare360Letter extends EnrollmentLetterDefaultConfigs implements SelfEnrollmentLetter
{
    const CONTACT_PHONE = '501-833-4001';
    const CONTACT_FAX = '1-888-213-5007';
    public bool $disableButtons;
    public $enrollee;
    public $extraAddressValues;
    public $extraAddressValuesExists;
    public $practice;
    public $userEnrollee;

    /**
     * ContinuumFamilyCareLlcLetter constructor.
     * @param bool $hideButtons
     * @param array $baseLetter
     * @param Practice $practice
     * @param User $userEnrollee
     * @param bool $disableButton
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
        $letterViewParams['extraContactDetails'] = $this->extraContactDetails();

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
        $signature = asset(GeneratePrimaryCare360::ANITA_ARAB_SIGNATURE);
        return "<img src=$signature  alt='' style='width: 250px;'/>";
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
        ];

        return LettersHelper::extraAddressValues($extraProps, $practiceLocationArray);
    }

    private function extraContactDetails()
    {
        return [
            'phone' => self::CONTACT_PHONE,
            'fax' => self::CONTACT_FAX,
        ];
    }
}
