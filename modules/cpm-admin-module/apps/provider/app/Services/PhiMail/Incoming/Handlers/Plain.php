<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail\Incoming\Handlers;

class Plain extends BaseHandler
{
    public function handle()
    {
        $this->dm->body = $this->attachmentData;
        $this->dm->save();
    }
}
