<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Exceptions;

class DailyCallbackReportException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Record has lot of emails in body. Probably it is the daily callback report.');
    }
}
