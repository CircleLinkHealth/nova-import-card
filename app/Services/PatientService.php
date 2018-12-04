<?php namespace App\Services;

use App\Filters\PatientFilters;
use App\Http\Resources\UserCsvResource;
use App\Http\Resources\UserAutocompleteResource;
use App\Http\Resources\UserSafeResource;
use App\Patient;
use App\Practice;
use App\Repositories\PatientReadRepository;
use App\Repositories\PatientWriteRepository;
use App\Repositories\UserRepositoryEloquent;
use App\Services\CCD\CcdAllergyService;
use Carbon\Carbon;
use Excel;

class PatientService
{
    private $patientRepo;
    private $userRepo;
    private $allergyRepo;
    private $patientReadRepo;

    public function __construct(PatientWriteRepository $patientRepo, PatientReadRepository $patientReadRepo, CcdAllergyService $allergyService, UserRepositoryEloquent $userRepo)
    {
        $this->patientRepo = $patientRepo;
        $this->userRepo = $userRepo;
        $this->allergyService = $allergyService;
        $this->patientReadRepo = $patientReadRepo;
    }

    public function repo()
    {
        return $this->patientRepo;
    }
    
    public function readRepo()
    {
        return $this->patientReadRepo;
    }

    public function getPatientByUserId($userId)
    {
        return optional($this->userRepo->model()->with(['patientInfo'])->find($userId))->patientInfo;
    }

    public function getCcdAllergies($userId)
    {
        return $this->allergyService->patientAllergies($userId);
    }

    public function setStatus($userId, $status)
    {
        $this->repo()->setStatus($userId, Patient::ENROLLED);
    }

    public function patients(PatientFilters $filters)
    {
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

    public function excelReport($users)
    {
        $date = date('Y-m-d H:i:s');
        return Excel::create('CLH-Patients-' . $date, function ($excel) use ($date, $users) {
            $excel->setTitle('CLH Patients List');
            $excel->setCreator('CLH System')->setCompany('CircleLink Health');
            $excel->setDescription('CLH Patients List');

            $excel->sheet('Sheet 1', function ($sheet) use (
                $users
            ) {
                $practices = Practice::get()->keyBy('id');

                $i = 0;
                // header
                $sheet->appendRow([
                    'name',
                    'provider',
                    'program',
                    'ccmStatus',
                    'careplanStatus',
                    'dob',
                    'phone',
                    'age',
                    'registeredOn',
                    'ccm'
                ]);
                foreach ($users as $user) {
                    if ($i > 2000000) {
                        continue 1;
                    }
                    $practice = $practices->get($user['program_id']);
                    if (isset($user['patient_info'])) {
                        $patient = $user['patient_info'];
                        $careplan = $user['careplan'];

                        $sheet->appendRow([
                            $user['name'],
                            $user['billing_provider_name'],
                            $practice ? $practice['display_name'] : null,
                            $patient ? $patient['ccm_status'] : null,
                            $careplan ? $careplan['status'] : null,
                            $patient ? $patient['birth_date'] : null,
                            $user['phone'],
                            $patient ? ($patient['birth_date'] ? Carbon::parse($patient['birth_date'])->age : 0) : null,
                            $user['created_at'] ? Carbon::parse($user['created_at'])->format('Y-m-d') : null,
                            $patient ? gmdate('H:i:s', $user['ccm_time']) : null
                        ]);
                        $i++;
                    }
                }
            });
        })->export('xls');
    }
}
