<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/10/2018
 * Time: 8:56 PM
 */

namespace App\Services;


use App\Repositories\PatientReadRepository;
use App\Repositories\PatientWriteRepository;

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
     * Get paused patients whose letter has not been printed yet
     *
     * @return \Illuminate\Support\Collection|static
     */
    public function getPausedPatients()
    {
        return $this->patientReadRepository
            ->paused()
            ->pausedLetterNotPrinted()
            ->fetch()
            ->map(function ($patient) {
                return [
                    'id'           => $patient->id,
                    'patient_name' => $patient->getFullName(),
                    'first_name'   => $patient->getFirstName($patient->first_name),
                    'last_name'    => $patient->getLastName($patient->last_name),
                    'link'         => route('patient.careplan.print', ['patientId' => $patient->id]),
                    'reg_date'     => optional($patient->user_registered)->format('m/d/Y'),
                    'paused_date'  => optional($patient->date_paused)->format('m/d/Y'),
                    'provider'     => $patient->billingProviderName,
                    'program_name' => $patient->primaryPracticeName,
                ];
            });
    }

    /**
     * Make paused letters for the user id's provided.
     *
     * @param array $userIdsToPrint
     * @param bool $viewOnly | If true, it doesn't update paused letter printed date.
     *
     * @return null|string
     */
    public function makePausedLettersPdf(array $userIdsToPrint, bool $viewOnly = false)
    {
        $files = $this->patientReadRepository
            ->model()
            ->whereIn('id', $userIdsToPrint)
            ->get()
            ->map(function ($user) {
                $lang = strtolower($user->patientInfo->preferred_contact_language);

                $fullPathToLetter = $this->pdfService->createPdfFromView('patient.letters.pausedLetter', [
                    'patient' => $user,
                    'lang'    => $lang,
                ]);

                $pathToFlyer = public_path("assets/pdf/flyers/paused/$lang.pdf");

                $fullPathToPdf = $this->pdfService->mergeFiles([$fullPathToLetter, $pathToFlyer]);

                return $fullPathToPdf;
            });

        if ( ! $viewOnly) {
            $this->patientWriteRepository->updatePausedLetterPrintedDate($userIdsToPrint);
        }

        return $this->pdfService->mergeFiles($files->all());
    }
}