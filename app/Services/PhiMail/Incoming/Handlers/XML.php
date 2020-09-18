<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail\Incoming\Handlers;

use CircleLinkHealth\SharedModels\Entities\DirectMailMessage;
use CircleLinkHealth\Customer\Entities\EmrDirectAddress;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class XML extends BaseHandler
{
    /**
     * @throws \Exception
     */
    public static function guessPractice(string $from): ?int
    {
        $practiceIds = self::practicesFromDmAddress(EmrDirectAddress::where('address', $from)->get());

        if (1 === $practiceIds->count()) {
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

        if (1 === $practiceIds->count()) {
            return $practiceIds->first();
        }

        if ($practiceIds->count() > 1) {
            throw new \Exception("DM Address `$from` belongs to more than one practice [{$practiceIds->implode(',')}]");
        }

        return null;
    }

    public function handle()
    {
        if (false === stripos($this->attachmentData, '<ClinicalDocument')) {
            return;
        }
        $this->storeCcda($this->attachmentData, $this->dm);
    }

    public static function mediaCollectionNameFactory()
    {
        return Ccda::CCD_MEDIA_COLLECTION_NAME;
    }

    public static function practicesFromDmAddress($collection)
    {
        return $collection->map(
            function ($dm) {
                if ( ! class_exists($dm->emrDirectable_type)) {
                    return;
                }
                if ( ! is_numeric($dm->emrDirectable_id)) {
                    return;
                }
                $obj = $dm->emrDirectable_type::find($dm->emrDirectable_id);

                if ( ! $obj) {
                    return null;
                }

                return $obj->program_id ?? $obj->practiceId ?? null;
            }
        )->filter()->unique()->values();
    }

    /**
     * Stores and imports a CCDA.
     *
     * @param $attachment
     */
    private function storeCcda(
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

        //save practice id after creting CCDA because guess practice may throw an exception
        if ($practiceId = self::guessPractice($dm->from)) {
            $ccda->practice_id = $practiceId;
            $ccda->save();
        }
    }
}
