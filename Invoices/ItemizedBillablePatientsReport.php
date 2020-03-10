<?php


namespace CircleLinkHealth\Customer\Invoices;


use App\Repositories\BillablePatientsEloquentRepository;
use App\Repositories\PatientSummaryEloquentRepository;
use App\ValueObjects\PatientReportData;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;

class ItemizedBillablePatientsReport
{
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
    protected   $practice;
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
        $this->practiceId = $practiceId;
        $this->month = $month;
        $this->practiceName = $practiceName;
    }
    
    private function practice() {
        if (! $this->practice) {
            $this->practice = Practice::findOrFail($this->practiceId);
        }
        
        return $this->practice;
    }

    public function toArray() {
        $data          = [];
        $data['name']  = $this->practiceName;
        $data['month'] = $this->month->toDateString();
    
        $repo = app(PatientSummaryEloquentRepository::class);
        $billablePatientsRepo = app(BillablePatientsEloquentRepository::class);
    
        $billablePatientsRepo->billablePatientSummaries($this->practiceId, $this->month, true)
                             ->where('approved', '=', true)
                             ->with([
                                        'patient.billingProvider.user',
                                        'patient.patientInfo.location'
                                    ])
                             ->has('patient.billingProvider.user')
                             ->has('patient.patientInfo.location')
                             ->chunkById(100,
                                 function ($summaries) use (&$data, $repo) {
                                     $summaries->each(function (PatientMonthlySummary $summary)  use (&$data, $repo){
                                         $u = $summary->patient;
                
                                         if ( ! $repo->hasBillableProblemsNameAndCode($summary)) {
                                             $summary = $repo->fillBillableProblemsNameAndCode($summary);
                                             $summary->save();
                                         }
                
                                         $patientData = new PatientReportData();
                                         $patientData->setCcmTime(round($summary->ccm_time / 60, 2));
                                         $patientData->setBhiTime(round($summary->bhi_time / 60, 2));
                                         $patientData->setName($u->getFullName());
                                         $patientData->setDob($u->getBirthDate());
                                         $patientData->setPractice($u->program_id);
                                         $patientData->setProvider($u->getBillingProviderName());
                                         $patientData->setBillingCodes($u->billingCodes($this->month));
                
                                         $patientData->setCcmProblemCodes($summary->getAttestedProblemsForReport());
                
                                         $patientData->setBhiCode(
                                             optional(optional($summary->billableProblems->first())->pivot)->icd_10_code
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
}