<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/16/20
 * Time: 11:21 PM
 */

namespace App\Services\PhiMail\Incoming\Handlers;


use App\Exports\PracticeReports\Mediable;
use Carbon\Carbon;

class Pdf extends BaseHandler implements Mediable
{
    public function handle()
    {
        $this->storeAsMedia();
    }
    
    public function mediaCollectionName() : string
    {
        return "dm_{$this->dm->id}_attachments_pdf";
    }
    
    public function filename() : string
    {
        return 'dm_id_'.$this->dm->id.'_attachment_'.Carbon::now()->toAtomString().'.pdf';
    }
    
    private function storeAsMedia()
    {
        $path = $this->fullPath();
    
        file_put_contents($path, $this->showRes->data);
    
        $this->dm->addMedia($path)
                 ->toMediaCollection($this->mediaCollectionName());
    }
    
    /**
     * Get the fullpath.
     *
     * @return string
     */
    public function fullPath(): string
    {
        return storage_path($this->filename());
    }
}