<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\SurveyInstance;

class SurveyInstanceObserver
{
    /**
     * Listen to the Instance saved event.
     */
    public function saved(SurveyInstance $instance)
    {
        //replaced by event and listener, keeping class in case we need
    }
}
