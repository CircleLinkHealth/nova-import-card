<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\CpmAdmin\Mail\SalesPracticeReport;
use CircleLinkHealth\CpmAdmin\Reports\Sales\Location\SalesByLocationReport;
use CircleLinkHealth\CpmAdmin\Reports\Sales\Practice\SalesByPracticeReport;
use CircleLinkHealth\CpmAdmin\Reports\Sales\Provider\SalesByProviderReport;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\PdfService\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;

class SalesReportsController extends Controller
{

}
