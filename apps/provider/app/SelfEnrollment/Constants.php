<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment;

class Constants
{
    /**
     * The number of days that need to pass after we send the initial invite, before we are can send the first reminder.
     */
    const DAYS_AFTER_FIRST_INVITE_TO_SEND_FIRST_REMINDER = 2;

    /**
     * The number of days that need to pass after we send the initial invite, before we are can send the second reminder.
     */
    const DAYS_AFTER_FIRST_INVITE_TO_SEND_SECOND_REMINDER = 4;

    /**
     * The number of days that need to pass after we send the initial invite, before we are can send the third reminder.
     */
    const DAYS_DIFF_FROM_FIRST_INVITE_TO_FINAL_ACTION = 6;
}
