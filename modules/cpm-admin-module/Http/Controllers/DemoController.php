<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use CircleLinkHealth\Core\Contracts\DirectMail;
use CircleLinkHealth\Core\Contracts\Efax;
use CircleLinkHealth\Core\StringManipulation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class DemoController extends Controller
{
    public function sendSampleEfaxNote(Request $request, Efax $fax)
    {
        $number  = (new StringManipulation())->formatPhoneNumberE164($request->input('fax_number'));
        $faxTest = $fax->createFaxFor($number)
            ->setOption('file', [base_path('sample-note.pdf')])
            ->send();
        dd($faxTest);
    }

    public function sendSampleEMRNote(Request $request, DirectMail $dm)
    {
        $test = $dm->send(
            $request->input('direct_address'),
            base_path('sample-note.pdf'),
            'sample-note.pdf'
        );
        dd($test);
    }

    public function sentry()
    {
        throw new \Exception('My first Sentry error!');
    }

    public function sentryLog()
    {
        Log::error('Log that should reach both stderr and Sentry!');

        return response([]);
    }
}
