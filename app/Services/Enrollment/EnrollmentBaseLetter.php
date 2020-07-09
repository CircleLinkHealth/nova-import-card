<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class EnrollmentBaseLetter extends Controller
{
    /**
     * @var Enrollee
     */
    private $enrollee;
    /**
     * @var bool
     */
    private $hideButtons;

    /**
     * @var bool
     */
    private $isSurveyOnlyUser;
    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    private $letter;
    /**
     * @var Practice
     */
    private $practice;
    /**
     * @var string
     */
    private $practiceLetterView;
    /**
     * @var User|null
     */
    private $provider;
    /**
     * @var User
     */
    private $user;

    /**
     * EnrollmentBaseLetter constructor.
     */
    public function __construct(Practice $practice, User $user, bool $isSurveyOnlyUser, Enrollee $enrollee, bool $hideButtons, string $practiceLetterView)
    {
        $this->practice           = $practice;
        $this->user               = $user;
        $this->isSurveyOnlyUser   = $isSurveyOnlyUser;
        $this->enrollee           = $enrollee;
        $this->hideButtons        = $hideButtons;
        $this->provider           = $user->billingProviderUser();
        $this->practiceLetterView = $practiceLetterView;
    }

    /**
     * @param $enrollablePrimaryPractice
     * @param $isSurveyOnlyUser
     * @param null $provider
     * @param bool $hideButtons
     *
     * @return mixed
     */
    public function composeBaseLetter(
        Model $letter,
        User $userForEnrollment,
        Practice $enrollablePrimaryPractice,
        $provider = null,
        $hideButtons = false
    ) {
        // CA's phone numbers is the practice number
        $practiceNumber = $enrollablePrimaryPractice->outgoing_phone_number;
        if ($practiceNumber) {
            //remove +1 from phone number
            $formatted      = formatPhoneNumber($practiceNumber);
            $practiceNumber = "<a href='tel:$formatted'>$formatted</a>";
        }

        if (null === $provider) {
            $provider = $userForEnrollment->billingProviderUser();
        }

        return $this->replaceLetterVars(
            $enrollablePrimaryPractice,
            $letter,
            $practiceNumber,
            $provider,
            $hideButtons
        );
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getBaseLetter()
    {
        $this->letter = $this->getPracticeLetter();

        if (empty($this->letter)) {
            try {
                $className = ucfirst(Str::camel('generate'.'-'.$this->practice->name.'-letter'));
                Artisan::call("module:seed --class=$className Eligibility");
            } catch (\Exception $exception) {
                throw $exception;
            }
            $this->letter = $this->getPracticeLetter();
        }

        $letterPages = $this->composeBaseLetter(
            $this->letter,
            $this->user,
            $this->practice,
            $this->provider,
            $this->hideButtons
        );

        return [
            'letter'           => $this->letter,
            'letterPages'      => $letterPages,
            'provider'         => $this->provider,
            'enrollee'         => $this->enrollee,
            'isSurveyOnlyUser' => $this->isSurveyOnlyUser,
        ];
    }

    /**
     * @param $practice
     * @param $practiceNumber
     * @param bool $hideButtons
     *
     * @return array
     */
    public function replaceLetterVars(Practice $practice, Model $practiceLetter, $practiceNumber, User $provider, $hideButtons = false)
    {
        $varsToBeReplaced = [
            EnrollmentInvitationLetter::PROVIDER_LAST_NAME,
            EnrollmentInvitationLetter::PRACTICE_NUMBER,
            EnrollmentInvitationLetter::SIGNATORY_NAME,
            EnrollmentInvitationLetter::PRACTICE_NAME,
            EnrollmentInvitationLetter::LOCATION_ENROLL_BUTTON,
            EnrollmentInvitationLetter::CUSTOMER_SIGNATURE_PIC,
            EnrollmentInvitationLetter::OPTIONAL_PARAGRAPH,
            EnrollmentInvitationLetter::LOCATION_ENROLL_BUTTON_SECOND,
            EnrollmentInvitationLetter::OPTIONAL_TITLE,
        ];

        $buttonsLocation = ! $hideButtons
            ? 'To enroll, click the "Get My Care Coach" button located both at the top and bottom of this letter.'
            : '';

        $optionalParagraph = ! $hideButtons
            ? "If you would like additional information, or are interested in enrolling today, please call <strong>$practiceNumber</strong>."
            : '';

        $buttonsSecondVersion = ! $hideButtons
            ? 'or click the "Get My Care Coach" button located both at the top and bottom of this letter.'
            : '';

        $optionalTitle = ! $hideButtons
            ? 'How do I sign up?'
            : '';

        $practiceSigSrc = $this->getPracticeSignatures($practiceLetter);

        // order has to be the same as the $varsToBeReplaced
        $replacementVars = [
            $provider->last_name,
            $practiceNumber,
            $provider->display_name,
            $practice->display_name,
            $buttonsLocation,
            $practiceSigSrc,
            $optionalParagraph,
            $buttonsSecondVersion,
            $optionalTitle,
        ];

        $letter = json_decode($practiceLetter->letter) ?? [];

        return $this->letterPages($letter, $varsToBeReplaced, $replacementVars);
    }

    private function getPracticeLetter()
    {
        return EnrollmentInvitationLetter::where('practice_id', $this->practice->id)->first();
    }

    private function getPracticeSignatures(Model $practiceLetter)
    {
        return $this->practiceLetterView::signatures($practiceLetter, $this->practice, $this->provider);
    }

    private function letterPages(object $letter, array $varsToBeReplaced, array $replacementVars)
    {
        $letterPages = [];
        foreach ($letter as $page) {
            $body          = $page->body;
            $letterPages[] = str_replace($varsToBeReplaced, $replacementVars, $body);
        }

        return $letterPages;
    }
}
