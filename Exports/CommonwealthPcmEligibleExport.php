<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/15/20
 * Time: 12:27 AM
 */

namespace CircleLinkHealth\Eligibility\Exports;

use App\Exports\PracticeReports\PracticeReportInterface;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\PcmProblem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithCustomQuerySize;

class CommonwealthPcmEligibleExport extends PracticeReportInterface implements WithCustomQuerySize
{
    const COMMONWEALTH_PAIN_PRACTICE_ID = 1;
    
    /**
     * @return string
     */
    public function filename(): string
    {
        if ( ! $this->filename) {
            $generatedAt    = now()->toDateTimeString();
            $this->filename = "Commonwealth Pain PCM Eligible Patients export generated at $generatedAt.csv";
        }
    
        return $this->filename;
    }
    
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'pcm_problem_code',
            'pcm_problem_code_type',
            'pcm_problem_description',
            'eligibility_job_id',
            'cpm_patient_id',
            'athenahealth_id',
            'medical_record_type',
            'medical_record_id',
            'mrn',
            'first_name',
            'last_name',
            'address',
            'address_2',
            'city',
            'state',
            'zip',
            'primary_phone',
            'other_phone',
            'home_phone',
            'cell_phone',
            'email',
            'dob',
            'lang',
            'primary_insurance',
            'secondary_insurance',
            'tertiary_insurance',
            'last_encounter',
            'referring_provider_name',
            'all_pcm_problems',
        ];
    }
    
    /**
     * @param mixed $eligibilityJob
     *
     * @return array
     */
    public function map($eligibilityJob): array
    {
        $problems = PcmProblem::whereIn(
            'id',
            $eligibilityJob->data['chargeable_services_codes_and_problems']['G2065']
        )->get();
    
        return [
            'pcm_problem_code'        => optional($problems->first())->code,
            'pcm_problem_code_type'   => optional($problems->first())->code_type,
            'pcm_problem_description' => optional($problems->first())->description,
            'eligibility_job_id'      => $eligibilityJob->id,
            'cpm_patient_id'          => optional($eligibilityJob->targetPatient)->user_id,
            'athenahealth_id'         => optional($eligibilityJob->targetPatient)->ehr_patient_id,
            'medical_record_type'     => $eligibilityJob->data['medical_record_type'] ?? '',
            'medical_record_id'       => $eligibilityJob->data['medical_record_id'] ?? '',
            'mrn'                     => $eligibilityJob->data['mrn_number'] ?? '',
            'first_name'              => $eligibilityJob->data['first_name'],
            'last_name'               => $eligibilityJob->data['last_name'],
            'address'                 => $eligibilityJob->data['street'] ?? '',
            'address_2'               => $eligibilityJob->data['street2'] ?? '',
            'city'                    => $eligibilityJob->data['city'],
            'state'                   => $eligibilityJob->data['state'],
            'zip'                     => $eligibilityJob->data['zip'],
            'primary_phone'           => $eligibilityJob->data['primary_phone'] ?? '',
            'other_phone'             => $eligibilityJob->data['other_phone'] ?? '',
            'home_phone'              => $eligibilityJob->data['home_phone'] ?? '',
            'cell_phone'              => $eligibilityJob->data['cell_phone'] ?? '',
            'email'                   => $eligibilityJob->data['email'] ?? '',
            'dob'                     => $eligibilityJob->data['dob'],
            'lang'                    => $eligibilityJob->data['language'] ?? '',
            'primary_insurance'       => $eligibilityJob->data['primary_insurance'],
            'secondary_insurance'     => $eligibilityJob->data['secondary_insurance'],
            'tertiary_insurance'      => $eligibilityJob->data['tertiary_insurance'],
            'last_encounter'          => $eligibilityJob->data['last_encounter'] ?? '',
            'referring_provider_name' => $eligibilityJob->data['referring_provider_name'],
        
            'all_pcm_problems'        => $problems->toJson(),
        ];
    }
    
    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return EligibilityJob::whereJsonLength(
            'data->chargeable_services_codes_and_problems->G2065',
            '>',
            0
        )->whereHas(
            'batch',
            function ($q) {
                $q->where('practice_id', self::COMMONWEALTH_PAIN_PRACTICE_ID);
            }
        )->with('targetPatient');
    }
    
    public function mediaCollectionName(): string
    {
        return 'pcm_eligible_patients_report_from_all_batches';
    }
    
    /**
     * Queued exportables are processed in chunks; each chunk being a job pushed to the queue by the QueuedWriter.
     * In case of exportables that implement the FromQuery concern, the number of jobs is calculated by dividing the $query->count() by the chunk size.
     * Depending on the implementation of the query() method (eg. When using a groupBy clause), this calculation might not be correct.
     *
     * When this is the case, you should use this method to provide a custom calculation of the query size.
     *
     * @return int
     */
    public function querySize(): int
    {
        return 50;
    }
}