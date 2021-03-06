<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Routing\Controller;

abstract class EnrollmentLetterDefaultConfigs extends Controller
{
    public function constructorDefaultArguments(bool $hideButtons, array $baseLetter, Practice $practice, User $userEnrollee)
    {
        $this->hideButtons      = $hideButtons;
        $this->baseLetter       = $baseLetter;
        $this->practice         = $practice;
        $this->userEnrollee     = $userEnrollee;
        $this->provider         = $this->baseLetter['provider'];
        $this->letterPages      = $this->baseLetter['letterPages'];
        $this->enrollee         = $this->baseLetter['enrollee'];
        $this->isSurveyOnlyUser = $this->baseLetter['isSurveyOnlyUser'];
    }

    /**
     * @return array
     */
    public function viewConfigurations(Practice $practice, Enrollee $enrollee)
    {
        $dateLetterSent = '???';
        $buttonColor    = SelfEnrollmentController::DEFAULT_BUTTON_COLOR;
        $className      = SelfEnrollmentController::getLetterClassName($practice->name);

        /** @var EnrollableInvitationLink $invitationLink */
        $invitationLink = $enrollee->getLastEnrollmentInvitationLink();
        if ($invitationLink) {
            $dateLetterSent = Carbon::parse($invitationLink->updated_at)->toDateString();
            $buttonColor    = $invitationLink->button_color;
        }

        return [
            'dateLetterSent' => $dateLetterSent,
            'buttonColor'    => $buttonColor,
            'className'      => $className,
        ];
    }
}
