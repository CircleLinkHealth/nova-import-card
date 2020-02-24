<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/16/20
 * Time: 11:21 PM
 */

namespace App\Services\PhiMail\Incoming\Handlers;


use Carbon\Carbon;

class Unknown extends BaseHandler
{
    public function handle()
    {
        $path = storage_path('dm_id_'.$this->dm->id.'_attachment_'.Carbon::now()->toAtomString());
        file_put_contents($path, $this->attachmentData);
        $this->dm->addMedia($path)
                 ->toMediaCollection("dm_{$this->dm->id}_attachments_unknown");
    }
}