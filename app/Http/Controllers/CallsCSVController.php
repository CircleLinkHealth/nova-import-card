<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class CallsCSVController extends Controller
{
    public function uploadCSV(Request $request)
    {
        if ($request->hasFile('uploadedCsv')) {
            $csv = parseCsvToArray($request->file('uploadedCsv'));

            return $csv;
        }
    }
}
