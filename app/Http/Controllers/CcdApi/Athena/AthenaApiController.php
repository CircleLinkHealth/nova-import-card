<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\CcdApi\Athena;

use App\Http\Controllers\Controller;
use CircleLinkHealth\Eligibility\Services\AthenaAPI\CcdService;
use CircleLinkHealth\Eligibility\Services\AthenaAPI\CreateAndPostPdfCareplan;
use Illuminate\Http\Request;

class AthenaApiController extends Controller
{
    /**
     * @var CcdService
     */
    private $athenaCcdService;
    private $service;

    public function __construct(CreateAndPostPdfCareplan $athenaApi, CcdService $athenaCcdService)
    {
        $this->service          = $athenaApi;
        $this->athenaCcdService = $athenaCcdService;
    }

    public function fetchCcdas(
        Request $request,
        $practiceId,
        $departmentId
    ) {
        if ($ids = null == $request->input('ids')) {
            return 'Please include IDs';
        }

        $ids = explode(',', $ids);
    }

    public function getCcdas(Request $request)
    {
        $ids = array_filter(explode(',', $request->input('ids')), 'trim');

        $imported = $this->athenaCcdService->importCcds($ids, $request->input('practice_id'));

        return count($imported).' CCDs were imported. To finish the importing process go to:  '.link_to_route('import.ccd.remix');
    }

    public function getTodays()
    {
        \Artisan::call('athena:getCcds');

        return 'athena:getCcds command ran.';
    }
}
