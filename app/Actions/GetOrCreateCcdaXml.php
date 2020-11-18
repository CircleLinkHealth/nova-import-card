<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Actions;

use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\CcdaBuilder\ClinicalDocument;
use CircleLinkHealth\CcdaBuilder\DataType\Collection\Set;
use CircleLinkHealth\CcdaBuilder\DataType\Identifier\InstanceIdentifier;
use CircleLinkHealth\CcdaBuilder\DataType\Name\EntityName;
use CircleLinkHealth\CcdaBuilder\DataType\TextAndMultimedia\CharacterString;
use CircleLinkHealth\CcdaBuilder\Elements\Title;
use CircleLinkHealth\CcdaBuilder\RIM\Entity\RepresentedCustodianOrganization;
use CircleLinkHealth\CcdaBuilder\RIM\Participation\Custodian;
use CircleLinkHealth\CcdaBuilder\RIM\Role\AssignedCustodian;

class GetOrCreateCcdaXml
{
    public static function forPatient(\CircleLinkHealth\Customer\Entities\User $patient)
    {
        $q = $patient->ccdas()->orderByDesc('id')->with('media');

        if ($ccda = $q->has('media')->first()) {
            return optional($ccda->getMedia('ccd')->first())->getFile();
        }

        return self::createXml($q->first());
    }

    private static function createXml(Ccda $ccda)
    {
        $bb = $ccda->bluebuttonJson();

        $doc = new ClinicalDocument();
        $doc->setTitle(
            new Title(new CharacterString('CarePlan Manager CCDA'))
        );
        self::setCustodian($doc);
    
        $xml = $doc->toDOMDocument();
        
        return $xml;
    }
    
    protected static function setCustodian(ClinicalDocument &$clinicalDocument)
    {
        $names = (new Set(EntityName::class))
            ->add(new EntityName('Care Plan Manager'));
        $ids = (new Set(InstanceIdentifier::class))
            ->add(new InstanceIdentifier('82112744-ea24-11e6-95be-17f96f76d55c'));
        
        $reprCustodian = new RepresentedCustodianOrganization($names, $ids);
        
        $assignedCustodian = new AssignedCustodian($reprCustodian);
    
        $clinicalDocument->setCustodian(new Custodian($assignedCustodian));
    }
}