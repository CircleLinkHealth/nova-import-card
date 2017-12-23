<?php

namespace App\Http\Controllers;

use App\CLH\Helpers\StringManipulation;
use App\Contracts\DirectMail;
use App\Contracts\Efax;
use App\Services\Phaxio\PhaxioService;
use App\Services\PhiMail\PhiMail;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function sendSampleEMRNote(Request $request, DirectMail $dm)
    {
        $test = $dm->send($request->input('direct_address'), public_path('assets/pdf/sample-note.pdf'), 'sample-note.pdf');
        dd($test);
    }

    public function sendSampleEfaxNote(Request $request, Efax $fax)
    {
        $number = (new StringManipulation())->formatPhoneNumberE164($request->input('fax_number'));
        $faxTest = $fax->send($number, public_path('assets/pdf/sample-note.pdf'));
        dd($faxTest);
    }
}
