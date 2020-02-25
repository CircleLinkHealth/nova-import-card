<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail\Incoming\Handlers;

use App\DirectMailMessage;
use App\Jobs\DecorateUPG0506CcdaWithPdfData;
use App\Jobs\ImportCcda;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class XML extends BaseHandler
{
    public function handle()
    {
        if (false === stripos($this->attachmentData, '<ClinicalDocument')) {
            return;
        }
        $this->storeAndImportCcd($this->attachmentData, $this->dm);
    }

    public static function mediaCollectionNameFactory()
    {
        return Ccda::CCD_MEDIA_COLLECTION_NAME;
    }

    /**
     * Stores and imports a CCDA.
     *
     * @param $attachment
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

        ImportCcda::withChain([
            new DecorateUPG0506CcdaWithPdfData($ccda),
        ])->dispatch($ccda)->onQueue('low');
    }
}
