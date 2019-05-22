<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Exports\FromArray;
use App\Filters\PatientFilters;
use App\Http\Resources\UserAutocompleteResource;
use App\Http\Resources\UserCsvResource;
use App\Http\Resources\UserSafeResource;
use App\Repositories\PatientReadRepository;
use App\Repositories\PatientWriteRepository;
use App\Repositories\UserRepositoryEloquent;
use App\Services\CCD\CcdAllergyService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PatientService
{
    private $allergyRepo;
    private $patientReadRepo;
    private $patientRepo;
    private $userRepo;

    public function __construct(
        PatientWriteRepository $patientRepo,
        PatientReadRepository $patientReadRepo,
        CcdAllergyService $allergyService,
        UserRepositoryEloquent $userRepo
    ) {
        $this->patientRepo     = $patientRepo;
        $this->userRepo        = $userRepo;
        $this->allergyService  = $allergyService;
        $this->patientReadRepo = $patientReadRepo;
    }

    public function excelReport($users)
    {
        if (is_a($users, LengthAwarePaginator::class)) {
            $users = $users->getCollection();
        }
        $date = date('Y-m-d H:i:s');

        $practices = Practice::get()->keyBy('id');

        $rows = [];

        $headings = [
            'name',
            'provider',
            'program',
            'ccmStatus',
            'careplanStatus',
            'withdrawnReason',
            'dob',
            'mrn',
            'phone',
            'age',
            'registeredOn',
            'ccm',
            'bhi',
        ];

        foreach ($users as $user) {
            $practice = $practices->get($user['program_id']);
            if (isset($user['patient_info'])) {
                $patient  = $user['patient_info'];
                $careplan = $user['careplan'];

                array_push(
                    $rows,
                    [
                        $user['name'],
                        $user['billing_provider_name'],
                        $practice
                            ? $practice['display_name']
                            : null,
                        $patient
                            ? $patient['ccm_status']
                            : null,
                        $careplan
                            ? $careplan['status']
                            : null,
                        $patient
                            ? $patient['withdrawn_reason']
                            : null,
                        $patient
                            ? $patient['birth_date']
                            : null,
                        $patient
                            ? $patient['mrn']
                            : null,
                        $user['phone'],
                        $patient
                            ? ($patient['birth_date']
                            ? Carbon::parse($patient['birth_date'])->age
                            : 0)
                            : null,
                        $user['created_at']
                            ? Carbon::parse($user['created_at'])->format('Y-m-d')
                            : null,
                        $patient
                            ? gmdate('H:i:s', $user['ccm_time'])
                            : null,
                        $patient
                            ? gmdate('H:i:s', $user['bhi_time'])
                            : null,
                    ]
                );
            }
        }
        $filename = 'CLH-Patients-'.$date.'.xls';

        return (new FromArray($filename, $rows, $headings))->download($filename);
    }

    public function getCcdAllergies(
        $userId
    ) {
        return $this->allergyService->patientAllergies($userId);
    }

    public function getPatientByUserId(
        $userId
    ) {
        return optional($this->userRepo->model()->with(['patientInfo'])->find($userId))->patientInfo;
    }

    public function patients(
        PatientFilters $filters
    ) {
        $users = $this->readRepo()->patients($filters);

        if ($filters->isAutocomplete()) {
            return UserAutocompleteResource::collection($users);
        }

        if ($filters->isCsv()) {
            return UserCsvResource::collection($users);
        }

        if ($filters->isExcel()) {
            return $this->excelReport($users);
        }

        return UserSafeResource::collection($users);
    }

    public function readRepo()
    {
        return $this->patientReadRepo;
    }

    public function repo()
    {
        return $this->patientRepo;
    }

    public function setStatus(
        $userId,
        $status
    ) {
        $this->repo()->setStatus($userId, Patient::ENROLLED);
    }
}
