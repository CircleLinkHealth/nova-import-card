<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates;

use CircleLinkHealth\ConditionCodeLookup\ConditionCodeLookup;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources\Problem;
use Illuminate\Support\Str;
use SimpleXMLElement;

class HtmlInXmlMedicalRecord extends BaseMedicalRecordTemplate
{
    private ?object $rawJson       = null;
    private ?SimpleXMLElement $xml = null;

    /**
     * @throws \Exception
     */
    public function __construct(?object $json, ?string $xml)
    {
        $this->xml     = new SimpleXMLElement($xml);
        $this->rawJson = $json;
        if ( ! $this->rawJson) {
            throw new \Exception('json is null');
        }
    }

    public function fillAllergiesSection(): array
    {
        return $this->getFromRaw('allergies');
    }

    public function fillDemographicsSection(): array
    {
        return $this->getFromRaw('demographics');
    }

    public function fillDocumentSection(): array
    {
        return $this->getFromRaw('document');
    }

    public function fillEncountersSection(): array
    {
        return $this->getFromRaw('encounters');
    }

    public function fillMedicationsSection(): array
    {
        return $this->getFromRaw('medications');
    }

    public function fillPayersSection(): array
    {
        return $this->getFromRaw('players');
    }

    public function fillProblemsSection(): array
    {
        $result = [];
        foreach ($this->xml->component->structuredBody->children() as $item) {
            $title = (string) $item->section->title;
            if ('PROBLEMS' !== $title) {
                continue;
            }

            foreach ($item->section->text->table->tbody->children() as $elem) {
                $problem    = new Problem();
                $children   = $elem->children();
                $probKind   = $children[0];
                $probStatus = $children[1];
                $startDate  = $children[2];
                $endDate    = $children[3];

                $this->setProblemCode($problem, $elem);
                $this->setProblemCodeSystemName($problem);
                $this->setProblemName($problem, $probKind);
                $this->setProblemStatus($problem, $probStatus);
                $this->setProblemStartDate($problem, $startDate);
                $this->setProblemEndDate($problem, $endDate);
                $result[] = $problem->toArray();
            }
        }

        return $result;
    }

    public function fillVitals(): array
    {
        return $this->getFromRaw('vitals');
    }

    public function getType(): string
    {
        return 'html-in-xml';
    }

    private function getFromRaw(string $key)
    {
        if (empty($this->rawJson->$key)) {
            return [];
        }

        return json_decode(json_encode($this->rawJson->$key), true);
    }

    private function lookupCodeSystemName(string $code): ?string
    {
        $resp = app(ConditionCodeLookup::class)->any($code);

        return $resp['type'] ?? null;
    }

    private function setProblemCode(Problem $problem, SimpleXMLElement $elem)
    {
        $code = (string) $elem['ID'];
        $code = (string) Str::replace('PROBSUMMARY_', '', $code);
        $problem->setCode($code);
    }

    private function setProblemCodeSystemName(Problem $problem)
    {
        $code           = $problem->getCode();
        $codeSystemName = $this->lookupCodeSystemName($code);
        $problem->setCodeSystemName($codeSystemName);
    }

    private function setProblemEndDate(Problem $problem, SimpleXMLElement $elem)
    {
        $this->trimAndSetField($problem, 'EndDate', $elem);
    }

    private function setProblemName(Problem $problem, SimpleXMLElement $elem)
    {
        $attr = (string) $elem['ID'];
        if ( ! Str::startsWith($attr, 'PROBKIND')) {
            return;
        }

        $name = (string) $elem;
        $name = $this->trimName($name);
        $problem->setName($name);
    }

    private function setProblemStartDate(Problem $problem, SimpleXMLElement $elem)
    {
        $this->trimAndSetField($problem, 'StartDate', $elem);
    }

    private function setProblemStatus(Problem $problem, SimpleXMLElement $elem)
    {
        $this->trimAndSetField($problem, 'Status', $elem);
    }

    private function trimAndSetField(Problem $problem, string $field, SimpleXMLElement $elem)
    {
        $value = (string) $elem;
        if (empty($value)) {
            return;
        }

        $value = $this->trimName($value);
        if (empty($value)) {
            return;
        }

        $setFuncName = 'set'.$field;
        $problem->$setFuncName($value);
    }

    private function trimName(string $name): string
    {
        $name = Str::startsWith($name, '*') ? Str::substr($name, 1) : $name;
        $name = Str::replace('&#160;', '', $name);
        $name = Str::replace("\u{00a0}", '', $name);

        return trim($name);
    }
}
