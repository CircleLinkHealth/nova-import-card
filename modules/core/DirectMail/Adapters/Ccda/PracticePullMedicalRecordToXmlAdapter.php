<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\DirectMail\Adapters\Ccda;

use CircleLinkHealth\Core\DirectMail\DTOs\CcdaEthnicityCodeMap;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\CcdToLogTranformer;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class PracticePullMedicalRecordToXmlAdapter
{
    private Ccda $ccda;

    public function __construct(Ccda $ccda)
    {
        $this->ccda = $ccda;
    }

    public function createAndStoreXml()
    {
        return $this->storeMedia($this->ccda, $this->createNewCcdaXml($this->ccda));
    }

    private function createNewCcdaXml(Ccda $ccda): ?string
    {
        $bb          = $ccda->bluebuttonJson();
        $transformer = new CcdToLogTranformer();
        $demos       = $transformer->demographics($bb->demographics);

        return view('ccda.xml', [
            'mrn'          => $bb->demographics->mrn_number,
            'street'       => $demos['street'],
            'street2'      => $demos['street2'],
            'city'         => $demos['city'],
            'state'        => $demos['state'],
            'zip'          => $demos['zip'],
            'firstName'    => $demos['first_name'],
            'lastName'     => $demos['last_name'],
            'dob'          => $demos['dob'],
            'language'     => $demos['language'] ?? 'eng',
            'cellPhone'    => formatPhoneNumberE164($demos['cell_phone']),
            'homePhone'    => formatPhoneNumberE164($demos['home_phone']),
            'workPhone'    => formatPhoneNumberE164($demos['work_phone']),
            'primaryPhone' => formatPhoneNumberE164($demos['primary_phone']),
            'email'        => $demos['email'],
            'ethnicity'    => $demos['ethnicity'],
            'raceCode'     => CcdaEthnicityCodeMap::codeFromText($demos['ethnicity']),
        ])->render();
    }

    private function storeMedia(Ccda $ccda, string $doc)
    {
        $filename = "ccda-{$ccda->id}.xml";
        $fullPath = storage_path(now()->timestamp.$filename);
        file_put_contents($fullPath, $doc);
        $ccda->addMedia($fullPath)
            ->preservingOriginal()
            ->toMediaCollection(Ccda::CCD_MEDIA_COLLECTION_NAME);

        return $fullPath;
    }
}
