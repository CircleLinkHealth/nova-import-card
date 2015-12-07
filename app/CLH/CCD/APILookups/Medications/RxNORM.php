<?php

namespace App\CLH\CCD\APILookups\Medications;

use GuzzleHttp\Client;

class RxNORM
{
    public $url = 'https://rxnav.nlm.nih.gov/REST/rxclass/class/byDrugName.json?drugName=';

    public function __construct()
    {
    }

    public function findByName($drugName)
    {
        $response = (array) json_decode(file_get_contents($this->url . $drugName), true);

        if (array_key_exists('rxclassDrugInfoList', $response)) {
            if (!empty($med = $response['rxclassDrugInfoList']['rxclassDrugInfo'][0]['rxclassMinConceptItem']['className'])) {
                return $med;
            }
        }
    }

}