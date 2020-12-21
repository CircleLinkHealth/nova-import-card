<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Services;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Repositories\PatientWriteRepository;
use CircleLinkHealth\PdfService\Services\PdfService;

class PrintPausedPatientLettersService
{
    private $patientReadRepository;
    private $patientWriteRepository;
    private $pdfService;

    public function __construct(
        PatientReadRepository $patientReadRepository,
        PdfService $pdfService,
        PatientWriteRepository $patientWriteRepository
    ) {
        $this->patientReadRepository  = $patientReadRepository;
        $this->patientWriteRepository = $patientWriteRepository;
        $this->pdfService             = $pdfService;
    }

    /**
     * Get paused patients whose letter has not been printed yet.
     *
     * @return \Illuminate\Support\Collection|static
     */
    public function getPausedPatients()
    {
        $isAdmin = auth()->user()->isAdmin();

        return $this->patientReadRepository
            ->paused()
            ->pausedLetterNotPrinted()
            ->fetch()
            ->map(
                function ($patient) use ($isAdmin) {
                    return [
                        'id'           => $patient->id,
                        'patient_name' => $patient->getFullName(),
                        'first_name'   => $patient->getFirstName(),
                        'last_name'    => $patient->getLastName(),
                        'link'         => $isAdmin ?
                            env('CPM_PROVIDER_APP_URL')."manage-patients/{$patient->id}/view-careplan" :
                            route('patient.careplan.print', ['patientId' => $patient->id]),
                        'reg_date'     => optional($patient->user_registered)->format('m/d/Y'),
                        'paused_date'  => optional($patient->getDatePaused())->format('m/d/Y'),
                        'provider'     => $patient->getBillingProviderName(),
                        'program_name' => $patient->getPrimaryPracticeName(),
                    ];
                }
            );
    }

    /**
     * Make paused letters for the user id's provided.
     *
     * @param bool $viewOnly | If true, it doesn't update paused letter printed date
     *
     * @return string|null
     */
    public function makePausedLettersPdf(array $userIdsToPrint, bool $viewOnly = false)
    {
        $files = User::ofType('participant')
            ->with('patientInfo')
            ->whereIn('id', $userIdsToPrint)
            ->get()
            ->map(
                function ($user) {
                    $lang = strtolower($user->getPreferredContactLanguage());

                    $fullPathToLetter = $this->pdfService->createPdfFromView(
                        'patient.letters.pausedLetter',
                        [
                            'patient' => $user,
                            'lang'    => $lang,
                        ],
                        null,
                        config('services.serverless-pdf-generator.mail-vendor-envelope-options')
                    );

                    $pathToFlyer = public_path("assets/pdf/flyers/paused/${lang}.pdf");

                    return $this->pdfService->mergeFiles([$fullPathToLetter, $pathToFlyer]);
                }
            );

        if ( ! $viewOnly) {
            $this->patientWriteRepository->updatePausedLetterPrintedDate($userIdsToPrint);
        }

        return $this->pdfService->mergeFiles($files->all());
    }
}
