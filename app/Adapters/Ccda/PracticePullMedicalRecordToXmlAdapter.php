<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Adapters\Ccda;

use CircleLinkHealth\CcdaBuilder\ClinicalDocument;
use CircleLinkHealth\CcdaBuilder\DataType\Collection\Set;
use CircleLinkHealth\CcdaBuilder\DataType\Identifier\InstanceIdentifier;
use CircleLinkHealth\CcdaBuilder\DataType\Name\EntityName;
use CircleLinkHealth\CcdaBuilder\DataType\TextAndMultimedia\CharacterString;
use CircleLinkHealth\CcdaBuilder\Elements\Title;
use CircleLinkHealth\CcdaBuilder\RIM\Entity\RepresentedCustodianOrganization;
use CircleLinkHealth\CcdaBuilder\RIM\Participation\Custodian;
use CircleLinkHealth\CcdaBuilder\RIM\Role\AssignedCustodian;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class PracticePullMedicalRecordToXmlAdapter
{
    private object $blueButtonJson;

    private function __construct(object $blueButtonJson)
    {
        $this->blueButtonJson = $blueButtonJson;
    }

    public static function fromCcda(Ccda $ccda)
    {
        return (new self($ccda->bluebuttonJson()))
            ->createAndStoreXml($ccda);
    }

    protected function setCustodian(ClinicalDocument &$clinicalDocument)
    {
        $names = (new Set(EntityName::class))
            ->add(new EntityName('Care Plan Manager'));
        $ids = (new Set(InstanceIdentifier::class))
            ->add(new InstanceIdentifier('82112744-ea24-11e6-95be-17f96f76d55c'));

        $reprCustodian = new RepresentedCustodianOrganization($names, $ids);

        $assignedCustodian = new AssignedCustodian($reprCustodian);

        $clinicalDocument->setCustodian(new Custodian($assignedCustodian));
    }

    private function newCpmXml(): ClinicalDocument
    {
        $doc = new ClinicalDocument();
        $doc->setTitle(
            new Title(new CharacterString('CarePlan Manager CCDA'))
        );
        $this->setCustodian($doc);
        
        return $doc;
    }

    private function storeMedia(Ccda $ccda, \DOMDocument $doc)
    {
        $filename = "ccda-{$ccda->id}.xml";
        $fullPath = storage_path(now()->timestamp.$filename);
        $doc->save($fullPath);
        $ccda->addMedia($fullPath)
            ->preservingOriginal()
            ->toMediaCollection(Ccda::CCD_MEDIA_COLLECTION_NAME);

        return $fullPath;
    }
    
    private function createAndStoreXml(Ccda $ccda)
    {
        $doc = $this->newCpmXml();
    
        return $this->storeMedia($ccda, $doc->toDOMDocument());
    }
}
