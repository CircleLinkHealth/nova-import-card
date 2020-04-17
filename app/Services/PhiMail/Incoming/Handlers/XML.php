<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail\Incoming\Handlers;

use App\DirectMailMessage;
use App\Jobs\DecorateUPG0506CcdaWithPdfData;
use App\Jobs\ImportCcda;
use CircleLinkHealth\Customer\Entities\EmrDirectAddress;
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
                'user_id' => null,
                'xml' => $attachment,
                'source' => Ccda::EMR_DIRECT,
            ]
        );
        
        //save practice id after creting CCDA because guess practice may throw an exception
        if ($practiceId = self::guessPractice($dm->from)) {
            $ccda->practice_id = $practiceId;
            $ccda->save();
        }
        
        ImportCcda::withChain(
            [
                new DecorateUPG0506CcdaWithPdfData($ccda),
            ]
        )->dispatch($ccda)->onQueue('low');
    }
    
    /**
     * @param string $from
     *
     * @return int|null
     * @throws \Exception
     */
    public static function guessPractice(string $from) :?int
    {
        $practiceIds = self::practicesFromDmAddress(EmrDirectAddress::where('address', $from)->get());
        
        if ($practiceIds->count() === 1) {
            return $practiceIds->first();
        }
        
        if ($practiceIds->count() > 1) {
            throw new \Exception("DM Address `$from` belongs to more than one practice [{$practiceIds->implode(',')}]");
        }
        
        $exploded = explode('@', $from);
        
        if (count($exploded) < 2) {
            return null;
        }
        
        $practiceIds = self::practicesFromDmAddress(
            EmrDirectAddress::where('address', 'like', "%$exploded[1]")->get()
        );
        
        if ($practiceIds->count() === 1) {
            return $practiceIds->first();
        }
        
        if ($practiceIds->count() > 1) {
            throw new \Exception("DM Address `$from` belongs to more than one practice [{$practiceIds->implode(',')}]");
        }
        
        return null;
    }
    
    public static function practicesFromDmAddress($collection)
    {
        return $collection->map(
            function ($dm) {
                if (!class_exists($dm->emrDirectable_type)) return;
                if (!is_numeric($dm->emrDirectable_id)) return;
                $obj = $dm->emrDirectable_type::find($dm->emrDirectable_id);
                
                if ( ! $obj) {
                    return null;
                }
                
                return $obj->program_id ?? $obj->practiceId ?? null;
            }
        )->filter()->unique()->values();
    }
}
