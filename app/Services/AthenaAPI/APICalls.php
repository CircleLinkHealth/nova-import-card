<?php

namespace App\Services\AthenaAPI;


class APICalls
{
    protected $api;
    protected $key;
    protected $secret;
    protected $version;
    protected $practiceid;

    public function __construct()
    {
        $this->key = env('ATHENA_KEY');
        $this->secret = env('ATHENA_SECRET');
        $this->version = env('ATHENA_VERSION');
        $this->practiceid = env('ATHENA_PRACTISE_ID');
        
        $this->api = new APIConnection($this->version, $this->key, $this->secret, $this->practiceid);
    }

    public function getCcd($patientId, $departmentId)
    {
        $ccda = $this->api->GET("patients/{$patientId}/ccda", [
            'patientid' => $patientId,
            'practiceid' => $this->practiceid,
            'departmentid' => $departmentId,
            'purpose' => 'internal',
            'xmloutput' => false,
        ]);

        return $ccda[0]['ccda'];
    }
}