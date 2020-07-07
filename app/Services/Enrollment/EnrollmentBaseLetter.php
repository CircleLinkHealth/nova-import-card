<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;

abstract class EnrollmentBaseLetter extends Controller
{
    public function baseLetterView(Practice $practice, User $user, bool $isSurveyOnlyUser, bool $hideButtons)
    {
        $provider = $user->billingProviderUser();
        /** @var EnrollmentInvitationLetter $letter */
        $letter = EnrollmentInvitationLetter::where('practice_id', $practice->id)
            ->firstOrFail();

        $letterPages = $this->composeEnrollmentLetter(
            $letter,
            $user,
            $practice,
            $isSurveyOnlyUser,
            $provider,
            $hideButtons
        );

        return [
            'letter'      => $letter,
            'letterPages' => $letterPages,
            'provider'    => $provider,
        ];
    }
}
