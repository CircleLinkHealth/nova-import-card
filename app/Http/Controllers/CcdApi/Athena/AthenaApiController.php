<?php

namespace App\Http\Controllers\CcdApi\Athena;

use App\Http\Controllers\Controller;
use App\Services\AthenaAPI\CcdService;
use App\Services\AthenaAPI\CreateAndPostPdfCareplan;
use Illuminate\Http\Request;

class AthenaApiController extends Controller
{
    private $service;
    /**
     * @var CcdService
     */
    private $athenaCcdService;

    public function __construct(CreateAndPostPdfCareplan $athenaApi, CcdService $athenaCcdService)
    {
        $this->service          = $athenaApi;
        $this->athenaCcdService = $athenaCcdService;
    }

    public function getTodays()
    {
        \Artisan::call('athena:getCcds');

        return 'athena:getCcds command ran.';
    }


    public function fetchCcdas(
        Request $request,
        $practiceId,
        $departmentId
    ) {
        if ($ids = $request->input('ids') == null) {
            return 'Please include IDs';
        }

        $ids = explode(',', $ids);
    }

    public function getCcdas(Request $request)
    {
        $ids = array_filter(explode(',', $request->input('ids')), 'trim');

        $imported = $this->athenaCcdService->importCcds($ids, $request->input('practice_id'));

        return count($imported) . " CCDs were imported. To finish the importing process go to:  " . link_to_route('import.ccd.remix');
    }
}
