<?php

namespace CircleLinkHealth\Eligibility\Http\Controllers;

use App\Console\Commands\CreateCommonwealthEligiblePatientsCsv;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class CommonwealthPCMController extends Controller
{
    public function downloadCsvList()
    {
        Artisan::queue(CreateCommonwealthEligiblePatientsCsv::class, ['practice_id' => 232, 'user_id' => auth()->id()]);
        
        return response()->json(['We received your request. We will email you when it is complete!']);
    }
}
