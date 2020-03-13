<?php

namespace CircleLinkHealth\Eligibility\Templates;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\MedicalRecord\ValueObjects\Problem;

class CommonwealthMedicalRecord
{
    /**
     * @var array
     */
    protected $ccda;
    /**
     * @var array
     */
    private $data;
    
    public function __construct(array $medicalRecord, object $ccda)
    {
        $this->data = $medicalRecord;
        $this->ccda = $ccda;
    }
    
    public function toArray()
    {
        return [
            'type'         => __CLASS__,
            'document'     => $this->fillDocumentSection($this->ccda->document),
            'allergies'    => $this->fillAllergiesSection($this->ccda->allergies),
            'demographics' => $this->fillDemographicsSection($this->ccda->demographics),
            'medications'  => $this->fillMedicationsSection($this->ccda->medications),
            'payers'       => $this->ccda->payers,
            'problems'     => $this->fillProblemsSection($this->ccda->problems),
            'vitals'       => $this->fillVitals($this->ccda->vitals),
        ];
    }
    
    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
    
    private function fillAllergiesSection($allergies)
    {
        return $allergies;
    }
    
    private function fillDemographicsSection($demographics)
    {
        return $demographics;
    }
    
    private function fillDocumentSection($document)
    {
        $document->custodian->name  = $this->getProviderName();
        $document->documentation_of = [
            [
                'provider_id' => null,
                'name'        => [
                    'prefix' => null,
                    'given'  => [
                        0 => $this->getProviderName(),
                    ],
                    'family' => '',
                    'suffix' => '',
                ],
                'phones'      => [
                    0 => [
                        'type'   => '',
                        'number' => '',
                    ],
                ],
                'address'     => [
                    'street'  => [
                        0 => '',
                    ],
                    'city'    => '',
                    'state'   => '',
                    'zip'     => '',
                    'country' => '',
                ],
            ]
        ];
        
        return $document;
    }
    
    private function fillMedicationsSection($medications)
    {
        return $medications;
    }
    
    private function fillProblemsSection($problems)
    {
        return array_merge($problems, $this->getMedicalHistory());
    }
    
    private function fillVitals($vitals)
    {
        return $vitals;
    }
    
    private function getAllergyName($allergy): string
    {
        return $allergy->Name;
    }
    
    public function getProviderName(): string
    {
        return $this->data['referring_provider_name'];
    }
    
    public function getMrn(): string
    {
        return $this->data['mrn_number'];
    }
    
    public function getFirstName(): string
    {
        return $this->data['first_name'];
    }
    
    public function getDob(): Carbon
    {
        return Carbon::parse($this->data['dob']);
    }
    
    public function getLastName(): string
    {
        return $this->data['last_name'];
    }
    
    private function getAddressLine1(): string
    {
        return $this->data['street'];
    }
    
    private function getAddressLine2(): string
    {
        return $this->data['street2'];
    }
    
    private function getZipCode(): string
    {
        return $this->data['zip'];
    }
    
    private function getMedicalHistory()
    {
        return collect($this->data['medical_history']['questions'] ?? [])->where('answer', 'Y')->pluck(
            'question'
        )->unique()
                                                                         ->map(
                                                                             function ($historyItem) {
                                                                                 return (new Problem())->setName($historyItem)->toObject();
                                                                             }
                                                                         )->all();
    }
}