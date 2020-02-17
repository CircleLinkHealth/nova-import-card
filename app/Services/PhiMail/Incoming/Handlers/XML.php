<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/16/20
 * Time: 11:16 PM
 */

namespace App\Services\PhiMail\Incoming\Handlers;


use App\DirectMailMessage;
use App\Jobs\ImportCcda;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class XML extends BaseHandler
{
    public static function mediaCollectionNameFactory()
    {
        return Ccda::CCD_MEDIA_COLLECTION_NAME;
    }
    
    public function handle()
    {
        if (false === stripos($this->attachmentData, '<ClinicalDocument')) return;
    
        $this->storeAndImportCcd($this->attachmentData, $this->dm);
    }
    
    /**
     * Stores and imports a CCDA.
     *
     * @param $attachment
     * @param DirectMailMessage $dm
     */
    private function storeAndImportCcd(
        string $attachment,
        DirectMailMessage $dm
    ) {
        $ccda = Ccda::create(
            [
                'direct_mail_message_id' => $dm->id,
                'user_id'                => null,
                'xml'                    => $attachment,
                'source'                 => Ccda::EMR_DIRECT,
            ]
        );
        
        ImportCcda::dispatch($ccda)->onQueue('low');
    }
}