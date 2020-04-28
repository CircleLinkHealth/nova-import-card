<?php

namespace App\Observers;

use App\SurveyInstance;

class SurveyInstanceObserver
{
    /**
     * Listen to the Instance saved event.
     *
     * @param SurveyInstance $instance
     */
    public function saved(SurveyInstance $instance)
    {

        //replaced by event and listener, keeping class in case we need
    }
}
