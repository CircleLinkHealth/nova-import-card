<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Adapters\Ccda;

use CircleLinkHealth\CcdaBuilder\ClinicalDocument;
use CircleLinkHealth\CcdaBuilder\DataType\Code\CodedValue;
use CircleLinkHealth\CcdaBuilder\DataType\Code\ConfidentialityCode as ConfidentialityCodeType;
use CircleLinkHealth\CcdaBuilder\DataType\Code\LoincCode;
use CircleLinkHealth\CcdaBuilder\DataType\Collection\Set;
use CircleLinkHealth\CcdaBuilder\DataType\Identifier\InstanceIdentifier;
use CircleLinkHealth\CcdaBuilder\DataType\Name\EntityName;
use CircleLinkHealth\CcdaBuilder\DataType\Name\PersonName;
use CircleLinkHealth\CcdaBuilder\DataType\Quantity\DateAndTime\TimeStamp;
use CircleLinkHealth\CcdaBuilder\DataType\TextAndMultimedia\CharacterString;
use CircleLinkHealth\CcdaBuilder\Elements\Code;
use CircleLinkHealth\CcdaBuilder\Elements\ConfidentialityCode;
use CircleLinkHealth\CcdaBuilder\Elements\EffectiveTime;
use CircleLinkHealth\CcdaBuilder\Elements\Id;
use CircleLinkHealth\CcdaBuilder\Elements\Title;
use CircleLinkHealth\CcdaBuilder\RIM\Entity\Patient;
use CircleLinkHealth\CcdaBuilder\RIM\Entity\RepresentedCustodianOrganization;
use CircleLinkHealth\CcdaBuilder\RIM\Participation\Author;
use CircleLinkHealth\CcdaBuilder\RIM\Participation\Custodian;
use CircleLinkHealth\CcdaBuilder\RIM\Participation\RecordTarget;
use CircleLinkHealth\CcdaBuilder\RIM\Role\AssignedCustodian;
use CircleLinkHealth\CcdaBuilder\RIM\Role\PatientRole;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
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
        $doc = $this->newCpmXml($this->ccda->id);

        return $this->storeMedia($this->ccda, $doc->toDOMDocument());
    }

    protected function patientIdentifierSection($id)
    {
        return (new Set(InstanceIdentifier::class))
            ->add(new InstanceIdentifier('2.16.840.1.113883.3.564.16521', $id));
    }

    protected function patientSection()
    {
        $names = new Set(PersonName::class);
        $names->add(
            (new PersonName())
                ->addPart(PersonName::FIRST_NAME, $this->ccda->patient_first_name)
                ->addPart(PersonName::LAST_NAME, $this->ccda->patient_last_name)
        );

        return new Patient(
            $names,
            new TimeStamp(ImportPatientInfo::parseDOBDate($this->ccda->bluebuttonJson()->demographics->dob)->toDateTime()),
            new CodedValue($this->ccda->bluebuttonJson()->demographics->gender, '', '2.16.840.1.113883.5.1', '')
        );
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

    protected function setRecordTarget(ClinicalDocument &$doc, $id)
    {
        $doc->setRecordTarget(
            new RecordTarget(
                new PatientRole(
                    $this->patientIdentifierSection($id),
                    $this->patientSection()
                )
            )
        );
    }

    private function newCpmXml($id): ClinicalDocument
    {
        $doc = new ClinicalDocument();
        $doc->setTitle(
            new Title(new CharacterString('CarePlan Manager CCDA'))
        );
        $now = new TimeStamp(now()->toDateTime());
        $doc->setEffectiveTime(new EffectiveTime($now));
        $doc->setId(new Id(new InstanceIdentifier('1.2.3.4', $id)));
        $doc->setCode(new Code(new LoincCode('18776-5', 'PLAN OF CARE')));
        $doc->setConfidentialityCode(new ConfidentialityCode(ConfidentialityCodeType::create(ConfidentialityCodeType::RESTRICTED_KEY, ConfidentialityCodeType::RESTRICTED)));
        $this->setRecordTarget($doc, $id);
//        $doc->setAuthor(new Author(
//            $now,
//            $this->getAssignedAuthor()
//        ));
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
}
