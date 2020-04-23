<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use App\Repositories\OpsDashboardPatientEloquentRepository;
use App\Services\OpsDashboardService;
use Carbon\Carbon;
use CircleLinkHealth\Core\Traits\AttachableAsMedia;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OpsDashboardPatientsReport implements FromCollection, Responsable, WithHeadings
{
    use AttachableAsMedia;
    use Exportable;
    /**
     * @var Carbon
     */
    protected $fromDate;

    /**
     * @var int
     */
    protected $practiceId;
    /**
     * @var string
     */
    protected $status;
    /**
     * @var Carbon
     */
    protected $toDate;
    /**
     * @var string
     */
    private $filename;
    /**
     * @var OpsDashboardPatientEloquentRepository
     */
    private $repo;
    /**
     * @var OpsDashboardService
     */
    private $service;

    public function __construct($practiceId, string $status, Carbon $fromDate, Carbon $toDate)
    {
        $this->practiceId = $practiceId ?? 'all';
        $this->status     = $status;
        $this->fromDate   = $fromDate;
        $this->toDate     = $toDate;
        $this->repo       = new OpsDashboardPatientEloquentRepository();
        $this->filename   = "Ops Dashboard Patients Report - ${fromDate} to ${toDate}.xls";
        $this->service    = new OpsDashboardService($this->repo);
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $data = collect();

        $patients = $this->repo->getPatientsByStatus($this->fromDate, $this->toDate);
        if ('all' != $this->practiceId) {
            $patients = $this->service->filterPatientsByPractice($patients, $this->practiceId);
        }

        if ('paused' == $this->status || 'withdrawn' == $this->status) {
            $patients = $this->service->filterPatientsByStatus($patients, $this->status);
        }

        foreach ($patients as $patient) {
            //collection
            $row = $this->makeExcelRow($patient, $this->fromDate, $this->toDate);
            if (null != $row) {
                $data->push($row->toArray());
            }
        }

        return $data;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function headings(): array
    {
        return [
            'Name',
            'DOB',
            'Practice Name',
            'Status',
            'Date Registered',
            'Date Paused/Withdrawn',
            'Enroller',
        ];
    }

    public function storeAndAttachMediaTo($model)
    {
        $filepath = 'exports/'.$this->getFilename();
        $stored   = $this->store($filepath, 'storage');

        return $this->attachMediaTo(
            $model,
            storage_path($filepath),
            "excel_report_for_{$this->fromDate->toDateString()}_to{$this->toDate->toDateString()}"
        );
    }

    /**
     * @return Collection
     */
    private function makeExcelRow(User $patient, Carbon $fromDate, Carbon $toDate)
    {
        if ($patient->patientInfo->registration_date >= $fromDate->toDateTimeString()
            && $patient->patientInfo->registration_date <= $toDate->toDateTimeString()
            && 'enrolled' != $patient->patientInfo->ccm_status) {
            $status       = $patient->patientInfo->ccm_status;
            $statusColumn = "Added - ${status} ";
        } else {
            $statusColumn = $patient->patientInfo->ccm_status;
        }

        if ('paused' == $patient->patientInfo->ccm_status) {
            $statusDate       = $patient->patientInfo->date_paused;
            $statusDateColumn = "Paused: ${statusDate}";
        } elseif ('withdrawn' == $patient->patientInfo->ccm_status) {
            $statusDate       = $patient->patientInfo->date_withdrawn;
            $statusDateColumn = "Withdrawn: ${statusDate}";
        } else {
            $statusDateColumn = '-';
        }

        $rowData = [
            'Name'                  => $patient->display_name,
            'DOB'                   => $patient->getBirthDate(),
            'Practice Name'         => $patient->getPrimaryPracticeName(),
            'Status'                => $statusColumn,
            'Date Registered'       => $patient->patientInfo->registration_date,
            'Date Paused/Withdrawn' => $statusDateColumn,
            'Enroller'              => '-',
        ];

        return collect($rowData);
    }
}
