<?php

namespace App\Listeners;

use App\Contracts\Repositories\CcdaRepository;
use App\Contracts\Repositories\CcdaRequestRepository;
use App\Events\CarePlanWasApproved;
use App\ForeignId;
use App\Models\CCD\CcdVendor;
use App\Services\AthenaAPI\Service;
use App\Services\ReportsService;
use Illuminate\Container\Container;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateAthenaPdfCarePlan
{
    /**
     * Create the event listener.
     *
     * @param CcdVendor $ccdVendor
     * @param CcdaRepository $ccdaRepository
     * @param CcdaRequestRepository $ccdaRequest
     * @param ReportsService $reportsService
     * @param Service $athenaService
     */
    public function __construct(CcdVendor $ccdVendor,
                                CcdaRepository $ccdaRepository,
                                CcdaRequestRepository $ccdaRequest,
                                ReportsService $reportsService,
                                Service $athenaService)
    {
        $this->ccdVendor = $ccdVendor;
        $this->ccdaRepository = $ccdaRepository;
        $this->ccdaRequest = $ccdaRequest;
        $this->reportsService = $reportsService;
        $this->athenaService = $athenaService;
    }

    /**
     * Handle the event.
     *
     * @param  CarePlanWasApproved $event
     * @return mixed|void
     * @internal param CcdVendor $ccdVendor
     * @internal param CcdaRepository $ccdaRepository
     * @internal param CcdaRequestRepository $ccdaRequest
     * @internal param ReportsService $reportsService
     * @internal param Service $athenaService
     */
    public function handle(CarePlanWasApproved $event)
    {
        if (!auth()->user()->hasRole(['provider'])) return;


        //If it's an Athena patient, send the PDF to Athena API
        $programId = auth()->user()->program_id;
        $user = $event->patient;

        if (isset($programId)) {
            $vendor = $this->ccdVendor->whereProgramId($programId)
                ->whereEhrName(ForeignId::ATHENA)
                ->whereNotNull('practice_id')
                ->first();

            if ($vendor) {
                $pathToPdf = $this->reportsService->makePdfCareplan($user);

                $ccda = $this->ccdaRepository->findWhere([
                    'patient_id' => $user->ID
                ])->first();

                $ccdaRequest = $this->ccdaRequest->findWhere([
                    'ccda_id' => $ccda->id,
                ])->first();

                if ($pathToPdf) {
                    $response = $this->athenaService->postPatientDocument($ccdaRequest->patient_id, $ccdaRequest->practice_id, $pathToPdf);

                    return json_decode($response, true);
                }
            }
        }
    }
}
