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
    const MEDIA_COLLECTION_NAME_STUB = 'dm_:dm_id:_attachments_pdf';
    
    public static function mediaCollectionNameFactory(int $id)
    {
        return str_replace(':dm_id:', $id, self::MEDIA_COLLECTION_NAME_STUB);
    }
    
    public function handle()
    {
        $this->storeAsMedia();
    }
    
    public function mediaCollectionName() : string
    {
        return self::mediaCollectionNameFactory($this->dm->id);
    }
    
    public function filename() : string
    {
        return 'dm_id_'.$this->dm->id.'_attachment_'.Carbon::now()->toAtomString().'.pdf';
    }
    
    private function storeAsMedia()
    {
        $path = $this->fullPath();
    
        file_put_contents($path, $this->attachmentData);
    
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