<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Contracts;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SelfEnrollment\Entities\User;
use Illuminate\Database\Eloquent\Model;

interface SelfEnrollmentLetter
{
    /**
     * abstract class EnrollmentLetterDefaultConfigs -> viewConfigurations().
     */
    public function getBaseViewConfigs(): array;

    public function letterBladeView();

    public function letterSpecificView();

    public static function signatures(Model $practiceLetter, Practice $practice, User $provider): string;
}