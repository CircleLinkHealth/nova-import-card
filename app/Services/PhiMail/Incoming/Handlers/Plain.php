<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/16/20
 * Time: 11:21 PM
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