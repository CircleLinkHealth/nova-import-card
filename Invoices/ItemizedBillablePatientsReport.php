<?php


namespace CircleLinkHealth\Customer\Invoices;


use App\Repositories\BillablePatientsEloquentRepository;
use App\Repositories\PatientSummaryEloquentRepository;
use App\ValueObjects\PatientReportData;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Illuminate\Support\Collection;

class ItemizedBillablePatientsReport
{
    const ATTACH_DEFAULT_PROBLEMS_FOR_MONTH = '2020-03-01';
    const BHI_SERVICE_CODE = 'CPT 99484';

    /**
     * @var int
     */
    protected $practiceId;
    /**
     * @var Carbon
     */
    protected $month;
    /**
     * @var Practice
     */
    protected $practice;
    /**
     * @var string
     */
    protected $practiceName;

    /**
     * ItemizedBillablePatientsReport constructor.
     *
     * @param int $practiceId
     * @param string $practiceName
     * @param Carbon $month
     */
    public function __construct(int $practiceId, string $practiceName, Carbon $month)
    {
        $this->practiceId   = $practiceId;
        $this->month        = $month;
        $this->practiceName = $practiceName;
    }

    private function practice()
    {
        if ( ! $this->practice) {
            $this->practice = Practice::findOrFail($this->practiceId);
        }

        return $this->practice;
    }

    public function toArray()
    {
        $data          = [];
        $data['name']  = $this->practiceName;
        $data['month'] = $this->month->toDateString();

        $repo                 = app(PatientSummaryEloquentRepository::class);
        $billablePatientsRepo = app(BillablePatientsEloquentRepository::class);

        $billablePatientsRepo->billablePatientSummaries($this->practiceId, $this->month, true)
                             ->where('approved', '=', true)
                             ->with([
                                 'patient.billingProvider.user',
                                 'patient.patientInfo.location',
                             ])
                             ->has('patient.billingProvider.user')
                             ->has('patient.patientInfo.location')
                             ->chunkById(100,
                                 function ($summaries) use (&$data, $repo) {
                                     $summaries->each(function (PatientMonthlySummary $summary) use (&$data, $repo) {
                                         $u = $summary->patient;

                                         $patientData = new PatientReportData();
                                         $patientData->setCcmTime(round($summary->ccm_time / 60, 2));
                                         $patientData->setBhiTime(round($summary->bhi_time / 60, 2));
                                         $patientData->setName($u->getFullName());
                                         $patientData->setDob($u->getBirthDate());
                                         $patientData->setPractice($u->program_id);
                                         $patientData->setProvider($u->getBillingProviderName());
                                         $patientData->setBillingCodes($u->billingCodes($this->month));

                                         $shouldAttachDefaultProblems = $summary->month_year->lte(Carbon::parse(self::ATTACH_DEFAULT_PROBLEMS_FOR_MONTH));

                                         $patientData->setCcmProblemCodes($this->getCcmAttestedConditions($summary,
                                             $shouldAttachDefaultProblems));

                                         $patientData->setBhiCodes(
                                             $this->getBhiAttestedConditions($summary, $shouldAttachDefaultProblems)
                                         );

                                         $patientData->setLocationName($u->getPreferredLocationName());

                                         $data['patientData'][$u->id] = $patientData;
                                     });
                                 });

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

    private function getCcmAttestedConditions(PatientMonthlySummary $summary, bool $shouldAttachDefaultProblems)
    {
        if ($shouldAttachDefaultProblems && $summary->attestedProblems->where('cpmProblem.is_behavioral', '=',
                false)->count() == 0) {
            return $this->formatProblemCodesForReport(collect([
                $summary->billableProblem1,
                $summary->billableProblem2,
            ])->filter());
        } else {
            return $this->getProblemCodesForReport($summary->attestedProblems, false);
        }
    }

    private function getBhiAttestedConditions(PatientMonthlySummary $summary, bool $shouldAttachDefaultProblems)
    {
        if (! $summary->hasServiceCode(self::BHI_SERVICE_CODE)) {
            return 'N/A';
        }

        if ($shouldAttachDefaultProblems && $summary->attestedProblems->where('cpmProblem.is_behavioral',
                '=',
                true)->count() == 0) {
            $bhiProblem = $summary->billableBhiProblems()->first();

            return $this->formatProblemCodesForReport(collect([
                $bhiProblem
                    ?: null,
            ])->filter());
        } else {
            return $this->getProblemCodesForReport($summary->attestedProblems, true);
        }


    }

    private function formatProblemCodesForReport(Collection $problems){
        return $problems->isNotEmpty()
            ? $problems->unique()->transform(function (Problem $problem) {
                return $problem->icd10Code();
            })->filter()->implode(', ')
            : 'N/A';
    }

    private function getProblemCodesForReport(Collection $problems, $isBhi){
        return $this->formatProblemCodesForReport($problems->where('cpmProblem.is_behavioral',
            '=', $isBhi));
    }
}