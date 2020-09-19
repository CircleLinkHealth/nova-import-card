<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Invoices;

use CircleLinkHealth\SharedModels\Repositories\BillablePatientsEloquentRepository;
use CircleLinkHealth\CpmAdmin\Repositories\PatientSummaryEloquentRepository;
use App\ValueObjects\PatientReportData;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Illuminate\Support\Collection;

class ItemizedBillablePatientsReport
{
    /**
     * @var Carbon
     */
    protected $month;
    /**
     * @var Practice
     */
    protected $practice;
    /**
     * @var int
     */
    protected $practiceId;
    /**
     * @var string
     */
    protected $practiceName;

    /**
     * ItemizedBillablePatientsReport constructor.
     */
    public function __construct(int $practiceId, string $practiceName, Carbon $month)
    {
        $this->practiceId   = $practiceId;
        $this->month        = $month;
        $this->practiceName = $practiceName;
    }

    public function toArray()
    {
        $data          = [];
        $data['name']  = $this->practiceName;
        $data['month'] = $this->month->toDateString();

        $repo                 = app(PatientSummaryEloquentRepository::class);
        $billablePatientsRepo = app(BillablePatientsEloquentRepository::class);

        $billablePatientsRepo->billablePatientSummaries($this->practiceId, $this->month, true)
            ->where([
                ['approved', '=', true],
                ['rejected', '=', false],
            ])
            ->with(
                [
                    'patient.billingProvider.user',
                    'patient.patientInfo.location',
                    'patient.ccdProblems' => function ($p) {
                        $p->with('cpmProblem', 'icd10Codes');
                    },
                ]
            )
            ->has('patient.billingProvider.user')
            ->has('patient.patientInfo.location')
            ->chunkById(
                100,
                function ($summaries) use (&$data, $repo) {
                    $summaries->each(
                        function (PatientMonthlySummary $summary) use (&$data, $repo) {
                            $u = $summary->patient;

                            $patientData = new PatientReportData();
                            $patientData->setCcmTime(round($summary->ccm_time / 60, 2));
                            $patientData->setBhiTime(round($summary->bhi_time / 60, 2));
                            $patientData->setName($u->getFullName());
                            $patientData->setDob($u->getBirthDate());
                            $patientData->setPractice($u->program_id);
                            $patientData->setProvider($u->getBillingProviderName());
                            $patientData->setBillingCodes($u->billingCodes($this->month));

                            $patientData->setCcmProblemCodes(
                                $this->getCcmAttestedConditions($summary)
                            );

                            $patientData->setAllCcmProblemCodes($summary);

                            $patientData->setBhiCodes($this->getBhiAttestedConditions($summary));

                            $patientData->setAllCcmProblemCodes($this->getAllCcmConditions(
                                $u,
                                $summary
                            ));

                            $patientData->setAllBhiCodes($this->getAllBhiConditions($u, $summary));

                            $patientData->setLocationName($u->getPreferredLocationName());

                            $data['patientData'][$u->id] = $patientData;
                        }
                    );
                }
            );

        $data['patientData'] = array_key_exists('patientData', $data)
            ? collect($data['patientData'])->sortBy(
                function ($data) {
                    return sprintf('%-12s%s', $data->getProvider(), $data->getName());
                }
            )
            : null;

        $awvPatients = User::ofType('participant')
            ->ofPractice($this->practiceId)
            ->whereHas(
                'patientAWVSummaries',
                function ($query) {
                    $query->where('is_billable', true)
                        ->where('year', $this->month->year);
                }
            )
            ->with(
                [
                    'patientAWVSummaries' => function ($q) {
                        $q->where('is_billable', true)
                            ->where('year', $this->month->year);
                    },
                    'billingProvider',
                ]
            )
            ->chunk(
                100,
                function ($patients) use (&$data) {
                    foreach ($patients as $u) {
                        $summary = $u->patientAWVSummaries->first();

                        $patientData = new PatientReportData();
                        $patientData->setName($u->getFullName());
                        $patientData->setDob($u->getBirthDate());
                        $patientData->setPractice($u->program_id);
                        $patientData->setProvider($u->getBillingProviderName());
                        $patientData->setAwvDate($summary->billable_at);

                        $data['awvPatientData'][$u->id] = $patientData;
                    }
                }
            );

        $data['awvPatientData'] = array_key_exists('awvPatientData', $data)
            ? collect($data['awvPatientData'])->sortBy(
                function ($data) {
                    return sprintf('%-12s%s', $data->getProvider(), $data->getName());
                }
            )
            : null;

        return $data;
    }

    public function toArrayForCsv(): array
    {
        $billablePatientsRepo = app(BillablePatientsEloquentRepository::class);

        $data = [];

        $billablePatientsRepo->billablePatients($this->practiceId, $this->month, null, true)
            ->with(
                [
                    'billingProvider.user',
                    'patientInfo.location',
                    'patientAWVSummaries' => function ($q) {
                        $q->where('is_billable', true)
                            ->where('year', $this->month->year);
                    },
                    'ccdProblems' => function ($p) {
                        $p->with(['cpmProblem', 'icd10Codes']);
                    },
                ]
            )
            ->has('billingProvider.user')
            ->has('patientInfo.location')
            ->chunkById(
                500,
                function ($summaries) use (&$data) {
                    $summaries->each(
                        function (User $patientUser) use (&$data) {
                            $summary = $patientUser->patientSummaries->sortByDesc('id')->first();

                            if ( ! $summary->approved) {
                                return;
                            }

                            $patientData = new PatientReportData();
                            $patientData->setCcmTime(
                                round($summary->ccm_time / 60, 2)
                            );
                            $patientData->setBhiTime(
                                round($summary->bhi_time / 60, 2)
                            );
                            $patientData->setName($patientUser->getFullName());
                            $patientData->setDob($patientUser->getBirthDate());
                            $patientData->setProvider(
                                $patientUser->getBillingProviderName()
                            );
                            $patientData->setBillingCodes(
                                $patientUser->billingCodes($this->month)
                            );
                            $patientData->setCcmProblemCodes($this->getCcmAttestedConditions($summary));

                            $patientData->setAllCcmProblemCodes($this->getAllCcmConditions(
                                $patientUser,
                                $summary
                            ));

                            $patientData->setBhiCodes($this->getBhiAttestedConditions($summary));

                            $patientData->setAllBhiCodes($this->getAllBhiConditions(
                                $patientUser,
                                $summary
                            ));

                            $patientData->setLocationName($patientUser->getPreferredLocationName());

                            $newRow = [
                                'Provider Name'        => $patientData->getProvider(),
                                'Location'             => $patientData->getLocationName(),
                                'Patient Name'         => $patientData->getName(),
                                'DOB'                  => $patientData->getDob(),
                                'Billing Code(s)'      => $patientData->getBillingCodes(),
                                'CCM Mins'             => $patientData->getCcmTime(),
                                'BHI Mins'             => $patientData->getBhiTime(),
                                'CCM Issue(s) Treated' => $patientData->getCcmProblemCodes(),
                                'All CCM Conditions'   => $patientData->getAllCcmProblemCodes(),
                                'BHI Issue(s) Treated' => $patientData->getBhiCodes(),
                                'All BHI Conditions'   => $patientData->getAllBhiCodes(),
                            ];

                            if ($patientUser->primaryPractice->hasAWVServiceCode() && $awvSummary = $patientUser->patientAWVSummaries->sortByDesc(
                                'id'
                            )->first()) {
                                $patientData->setAwvDate($awvSummary->billable_at);
                                $newRow['AWV Date'] = $patientData->getAwvDate();
                            }

                            $data[] = $newRow;
                        }
                    );
                }
            );

        return $data;
    }

    private function formatProblemCodesForReport(Collection $problems)
    {
        return $problems->isNotEmpty()
            ?
            $problems->map(
                function (Problem $problem) {
                    return $problem->icd10Code();
                }
            )->filter()
                ->unique()
                ->implode(', ')
            : 'N/A';
    }

    private function getAllBhiConditions(User $patient, PatientMonthlySummary $summary)
    {
        if ( ! $summary->hasServiceCode(ChargeableService::BHI)) {
            return 'N/A';
        }

        return $this->formatProblemCodesForReport(
            $patient->ccdProblems->where(
                'cpmProblem.is_behavioral',
                '=',
                true
            )
        );
    }

    private function getAllCcmConditions(User $patient, PatientMonthlySummary $summary)
    {
        $problems = $patient->ccdProblems->where(
            'cpm_problem_id',
            '!=',
            genericDiabetes()->id
        );

        if ($summary->hasServiceCode(ChargeableService::BHI)) {
            $problems = $problems->where('cpmProblem.is_behavioral', '=', false);
        }

        return $this->formatProblemCodesForReport($problems);
    }

    private function getBhiAttestedConditions(PatientMonthlySummary $summary)
    {
        if ( ! $summary->hasServiceCode(ChargeableService::BHI)) {
            return 'N/A';
        }

        return $this->formatProblemCodesForReport($summary->bhiAttestedProblems()->filter());
    }

    private function getCcmAttestedConditions(PatientMonthlySummary $summary)
    {
        return $this->formatProblemCodesForReport($summary->ccmAttestedProblems()->filter());
    }

    private function practice()
    {
        if ( ! $this->practice) {
            $this->practice = Practice::findOrFail($this->practiceId);
        }

        return $this->practice;
    }
}
