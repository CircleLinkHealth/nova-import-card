<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\XmlCCD;
use Illuminate\Http\Request;
use michalisantoniou6\PhpCCDParser\CCDParser;

class CCDParserDemoController extends Controller {

	public function index()
    {
        $xml = XmlCCD::find(424);

        debug($xml->ccd);

        $patient = new CCDParser($xml->ccd);

        echo '<pre>';

            echo $patient->getParsedCCD('json');

        echo '</pre>';
    }

}
