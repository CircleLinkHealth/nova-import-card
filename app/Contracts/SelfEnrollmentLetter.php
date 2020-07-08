<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;

interface SelfEnrollmentLetter
{
    public function letterBladeView();

    public function letterSpecificView(array $baseLetter, Practice $practice, User $userEnrollee);

    public static function signatures(Model $practiceLetter, Practice $practice, User $provider): string;
}
