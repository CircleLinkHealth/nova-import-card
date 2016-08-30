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
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CarePlanWasApproved $event
     * @return void
     */
    public function handle(CarePlanWasApproved $event,
                           CcdVendor $ccdVendor,
                           CcdaRepository $ccdaRepository,
                           CcdaRequestRepository $ccdaRequest,
                           ReportsService $reportsService,
                           Service $athenaService)
    {
        if (! auth()->user()->hasRole(['provider'])) return;


        //If it's an Athena patient, send the PDF to Athena API
        $programId = auth()->user()->program_id;
        $user = $event->patient;

        if (isset($programId)) {
            $vendor = $ccdVendor->whereProgramId($programId)
                ->whereEhrName(ForeignId::ATHENA)
                ->whereNotNull('practice_id')
                ->first();

            if ($vendor) {
                $pathToPdf = $reportsService->makePdfCareplan($user);

                $ccda = $ccdaRepository->findWhere([
                    'patient_id' => $user->ID
                ])->first();

                $ccdaRequest = $ccdaRequest->findWhere([
                    'ccda_id' => $ccda->id,
                ])->first();

                if ($pathToPdf) {
                    $response = $athenaService->postPatientDocument($ccdaRequest->patient_id, $ccdaRequest->practice_id, $pathToPdf);

                    return json_decode($response, true);
                }
            }
        }
    }
}
