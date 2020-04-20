<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Http\Controllers;

use App\Console\Commands\CreatePracticeReport;
use CircleLinkHealth\Eligibility\Exports\CommonwealthPcmEligibleExport;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class CommonwealthPCMController extends Controller
{
    public function downloadCsvList()
    {
        Artisan::queue(CreatePracticeReport::class, ['class' => CommonwealthPcmEligibleExport::class, 'practice_id' => 232, 'user_id' => auth()->id()]);

        return response()->json(['We received your request. We will email you when it is complete!']);
    }
}
