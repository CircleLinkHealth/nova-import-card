<?php

namespace App\Http\Controllers;

use App\Services\PhiMail\PhiMail;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function sendSampleEMRNote(Request $request) {
        $phiMail = new PhiMail();
        $test = $phiMail->send($request->input('direct_address'), public_path('assets/pdf/sample-note.pdf'), 'sample-note.pdf');
        dd($test);
    }
}
