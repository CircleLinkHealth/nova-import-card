<?php

namespace App\Http\Controllers\CcdApi\Athena;

use App\Services\AthenaAPI\APIConnection;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class AthenaApiController extends Controller
{
    protected $api;
    protected $key = '8q63pe2583be9pjctxxtcejp';
    protected $secret = 'HpFT8Smxe65mWTD';
    protected $version = 'preview1';
    protected $practiceid = 1959188;

    public function __construct()
    {
        $this->api = new APIConnection($this->version, $this->key, $this->secret, $this->practiceid);
    }
    
    public function getCcd()
    {
        $ccda = $this->api->GET('ccda', [
            'patientid' => 4185,
            'practiceid' => $this->practiceid,
            'departmentid' => 1,
            'purpose' => 'internal',
            'xmloutput' => false,
        ]);

        echo 'here';
    }
}
