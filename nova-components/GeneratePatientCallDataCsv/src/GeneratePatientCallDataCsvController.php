<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\GeneratePatientCallDataCsv;

use Illuminate\Http\Request;

class GeneratePatientCallDataCsvController
{
    public function handle(Request $request)
    {
        //validate input - custom request class?
        $date = \Carbon\Carbon::parse($request->input('month'));

        $data = $this->getData();
        //return media download
        return ['message' => 'test'];
    }

    private function getData()
    {
        //for current month get from calls view
        //for the past create custom query
        return [];
    }
}
